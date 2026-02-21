#!/usr/bin/env python3
"""
Verify Neo4j graph integrity after a sync.

Usage:
    # Take a snapshot BEFORE sync
    python scripts/verify_graph_sync.py --snapshot before

    # Run sync...

    # Compare AFTER sync
    python scripts/verify_graph_sync.py --snapshot after

    # Or just inspect current state
    python scripts/verify_graph_sync.py --inspect

    # Search for a specific class/file
    python scripts/verify_graph_sync.py --search dsa_vkyc_ref_check
"""

import os
import sys
import json
import argparse
from pathlib import Path

project_root = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(project_root))

# Load .env
env_path = project_root / ".env"
if env_path.exists():
    with open(env_path, "r") as f:
        for line in f:
            line = line.strip()
            if line and not line.startswith("#") and "=" in line:
                key, value = line.split("=", 1)
                if not os.environ.get(key):
                    os.environ[key] = value.strip('"\'')

from neo4j import GraphDatabase

SNAPSHOT_DIR = project_root / "scripts"


def get_driver():
    uri = os.environ.get("NEO4J_URI", "bolt://localhost:7687")
    user = os.environ.get("NEO4J_USER", "neo4j")
    pw = os.environ.get("NEO4J_PASSWORD", "")
    return GraphDatabase.driver(uri, auth=(user, pw))


def get_counts(driver):
    """Get all node and relationship counts."""
    with driver.session() as session:
        # Node counts
        result = session.run("MATCH (n) RETURN labels(n)[0] AS label, count(n) AS cnt ORDER BY label")
        nodes = {r["label"]: r["cnt"] for r in result}

        # Relationship counts
        result = session.run("MATCH ()-[r]->() RETURN type(r) AS rel, count(r) AS cnt ORDER BY rel")
        rels = {r["rel"]: r["cnt"] for r in result}

    return {"nodes": nodes, "relationships": rels}


def snapshot(driver, name: str):
    """Save current graph counts to a JSON file."""
    counts = get_counts(driver)
    path = SNAPSHOT_DIR / f"graph_snapshot_{name}.json"
    with open(path, "w") as f:
        json.dump(counts, f, indent=2)
    print(f"✅ Snapshot saved to {path}")
    print_counts(counts)


def compare():
    """Compare before and after snapshots."""
    before_path = SNAPSHOT_DIR / "graph_snapshot_before.json"
    after_path = SNAPSHOT_DIR / "graph_snapshot_after.json"

    if not before_path.exists() or not after_path.exists():
        print("❌ Need both 'before' and 'after' snapshots. Run:")
        print("   python scripts/verify_graph_sync.py --snapshot before")
        print("   # ... run sync ...")
        print("   python scripts/verify_graph_sync.py --snapshot after")
        return

    with open(before_path) as f:
        before = json.load(f)
    with open(after_path) as f:
        after = json.load(f)

    print("=" * 60)
    print("GRAPH DIFF: BEFORE vs AFTER SYNC")
    print("=" * 60)

    # Compare nodes
    all_labels = sorted(set(list(before["nodes"].keys()) + list(after["nodes"].keys())))
    print("\n📊 NODE CHANGES:")
    any_change = False
    for label in all_labels:
        b = before["nodes"].get(label, 0)
        a = after["nodes"].get(label, 0)
        diff = a - b
        if diff != 0:
            symbol = "+" if diff > 0 else ""
            print(f"  {label}: {b} → {a} ({symbol}{diff})")
            any_change = True
    if not any_change:
        print("  No changes ✅")

    # Compare relationships
    all_rels = sorted(set(list(before["relationships"].keys()) + list(after["relationships"].keys())))
    print("\n🔗 RELATIONSHIP CHANGES:")
    any_change = False
    for rel in all_rels:
        b = before["relationships"].get(rel, 0)
        a = after["relationships"].get(rel, 0)
        diff = a - b
        if diff != 0:
            symbol = "+" if diff > 0 else ""
            print(f"  {rel}: {b} → {a} ({symbol}{diff})")
            any_change = True
    if not any_change:
        print("  No changes ✅")


def inspect(driver):
    """Print current graph state."""
    counts = get_counts(driver)
    print_counts(counts)


def print_counts(counts):
    print("\n📊 NODE COUNTS:")
    for label, cnt in sorted(counts["nodes"].items(), key=lambda x: -x[1]):
        print(f"  {label}: {cnt}")
    print(f"\n🔗 RELATIONSHIP COUNTS:")
    for rel, cnt in sorted(counts["relationships"].items(), key=lambda x: -x[1]):
        print(f"  {rel}: {cnt}")


def search(driver, term: str):
    """Search for nodes and relationships involving a specific term."""
    print(f"\n🔍 Searching for '{term}' in Neo4j...\n")

    with driver.session() as session:
        # Find nodes
        result = session.run("""
            MATCH (n)
            WHERE n.id CONTAINS $term OR n.name CONTAINS $term OR n.file CONTAINS $term
            RETURN labels(n)[0] AS label, n.id AS id, n.name AS name, n.file AS file
            ORDER BY labels(n)[0]
        """, term=term)
        records = list(result)

        if records:
            print(f"📌 NODES matching '{term}': {len(records)}")
            for r in records:
                print(f"  [{r['label']}] id={r['id']}, name={r['name']}, file={r['file']}")
        else:
            print(f"  No nodes found matching '{term}'")

        # Find relationships involving those nodes
        result = session.run("""
            MATCH (a)-[r]->(b)
            WHERE a.id CONTAINS $term OR a.name CONTAINS $term
            RETURN labels(a)[0] AS from_label, a.id AS from_id,
                   type(r) AS rel_type,
                   labels(b)[0] AS to_label, b.id AS to_id
            ORDER BY type(r)
        """, term=term)
        outgoing = list(result)

        result = session.run("""
            MATCH (a)-[r]->(b)
            WHERE b.id CONTAINS $term OR b.name CONTAINS $term
            RETURN labels(a)[0] AS from_label, a.id AS from_id,
                   type(r) AS rel_type,
                   labels(b)[0] AS to_label, b.id AS to_id
            ORDER BY type(r)
        """, term=term)
        incoming = list(result)

        if outgoing:
            print(f"\n➡️  OUTGOING relationships ({len(outgoing)}):")
            for r in outgoing:
                print(f"  [{r['from_label']}:{r['from_id']}] --{r['rel_type']}--> [{r['to_label']}:{r['to_id']}]")
        else:
            print(f"\n  No outgoing relationships")

        if incoming:
            print(f"\n⬅️  INCOMING relationships ({len(incoming)}):")
            for r in incoming:
                print(f"  [{r['from_label']}:{r['from_id']}] --{r['rel_type']}--> [{r['to_label']}:{r['to_id']}]")
        else:
            print(f"\n  No incoming relationships")

        total = len(records) + len(outgoing) + len(incoming)
        if total == 0:
            print(f"\n✅ '{term}' has NO presence in the graph (expected if its directory isn't scanned)")


def main():
    parser = argparse.ArgumentParser(description="Verify Neo4j graph after sync")
    parser.add_argument("--snapshot", choices=["before", "after"], help="Save a snapshot")
    parser.add_argument("--compare", action="store_true", help="Compare before/after snapshots")
    parser.add_argument("--inspect", action="store_true", help="Show current graph state")
    parser.add_argument("--search", type=str, help="Search for a specific term in the graph")
    args = parser.parse_args()

    if args.compare:
        compare()
        return

    driver = get_driver()
    try:
        if args.snapshot:
            snapshot(driver, args.snapshot)
        elif args.search:
            search(driver, args.search)
        elif args.inspect:
            inspect(driver)
        else:
            parser.print_help()
    finally:
        driver.close()


if __name__ == "__main__":
    main()
