
import os
import sys
from neo4j import GraphDatabase

# Common passwords to test
PASSWORDS_TO_TRY = [
    "your_neo4j_password",
    "neo4j",
    "password",
    "12345678",
    "admin"
]

def test_connect(uri, user, password):
    print(f"Testing {user} / {password} ... ", end="")
    try:
        driver = GraphDatabase.driver(uri, auth=(user, password))
        driver.verify_connectivity()
        print("✅ SUCCESS!")
        return True
    except Exception as e:
        print(f"❌ Failed ({e})")
        return False
    finally:
        if 'driver' in locals():
            driver.close()

def main():
    print("🔍 Neo4j Auth Debugger")
    print("=======================")
    
    uri = "neo4j://127.0.0.1:7687" # From user screenshot
    user = "neo4j"
    
    success = False
    for password in PASSWORDS_TO_TRY:
        if test_connect(uri, user, password):
            print(f"\n🎉 FOUND VALID CREDENTIALS!")
            print(f"User: {user}")
            print(f"Password: {password}")
            
            # Check if it matches .env
            try:
                from dotenv import load_dotenv
                load_dotenv()
                env_pass = os.getenv("NEO4J_PASSWORD")
                if env_pass != password:
                    print(f"\n⚠️  mismatch: .env has '{env_pass}' but actual is '{password}'")
                    print("👉 Please update your .env file with the correct password.")
                else:
                    print("\n✅ Matches your .env configuration.")
            except ImportError:
                pass
                
            success = True
            break
            
    if not success:
        print("\n❌ Could not connect with any common password.")
        print("Please Reset Password in Neo4j Desktop or set NEO4J_PASSWORD in .env.")

if __name__ == "__main__":
    main()
