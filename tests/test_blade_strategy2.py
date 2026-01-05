#!/usr/bin/env python3
"""
Test Suite for Blade Strategy 2

Comprehensive tests for description-first retrieval system
"""

import os
import sys
from pathlib import Path
import time
import unittest

# Add parent directory to path
sys.path.append(str(Path(__file__).parent.parent))

from utils.smart_snippet_extractor import SmartSnippetExtractor
from utils.blade_description_engine import BladeDescriptionEngine


class TestSmartSnippetExtractor(unittest.TestCase):
    """Tests for SmartSnippetExtractor"""
    
    @classmethod
    def setUpClass(cls):
        """Initialize extractor once for all tests"""
        cls.extractor = SmartSnippetExtractor()
    
    def test_extractor_initialization(self):
        """Test that extractor initializes correctly"""
        self.assertIsNotNone(self.extractor)
        self.assertIsNotNone(self.extractor.model)
    
    def test_small_content_passthrough(self):
        """Test that small content is returned as-is"""
        small_content = "<div>Small content</div>"
        result = self.extractor.extract_relevant_snippet(
            small_content,
            "test query",
            max_chars=1000
        )
        self.assertEqual(result, small_content)
    
    def test_large_content_extraction(self):
        """Test extraction from large content"""
        large_content = """
        <form method="POST">
            @csrf
            <input type="text" name="test">
        </form>
        """ * 100  # Make it large
        
        result = self.extractor.extract_relevant_snippet(
            large_content,
            "CSRF protection",
            max_chars=500
        )
        
        # Should be truncated
        self.assertLess(len(result), len(large_content))
        self.assertLessEqual(len(result), 700)  # Allow some buffer
        
        # Should contain relevant content
        self.assertIn('csrf', result.lower())
    
    def test_form_focused_extraction(self):
        """Test form-focused extraction"""
        content = """
        <div>Other content</div>
        <form method="POST">
            @csrf
            <input type="email" name="email">
        </form>
        <div>More content</div>
        """
        
        result = self.extractor.extract_form_focused(
            content,
            "form submission",
            max_chars=200
        )
        
        # Should prioritize form
        self.assertIn('form', result.lower())
    
    def test_semantic_block_splitting(self):
        """Test that content is split into semantic blocks"""
        content = """
        <form id="form1">Form 1</form>
        <div class="content">Div 1</div>
        <script>console.log('test');</script>
        """
        
        blocks = self.extractor.split_into_semantic_blocks(content)
        
        # Should find multiple blocks
        self.assertGreater(len(blocks), 0)
        
        # Should have different block types
        block_types = set(b['type'] for b in blocks)
        self.assertIn('form', block_types)


class TestBladeDescriptionEngine(unittest.TestCase):
    """Tests for BladeDescriptionEngine"""
    
    @classmethod
    def setUpClass(cls):
        """Initialize engine once for all tests"""
        try:
            cls.engine = BladeDescriptionEngine()
            cls.engine_available = True
        except Exception as e:
            print(f"⚠️  Could not initialize engine: {e}")
            print("   Some tests will be skipped")
            cls.engine_available = False
    
    def setUp(self):
        """Skip tests if engine not available"""
        if not self.engine_available:
            self.skipTest("Engine not available (ChromaDB not found)")
    
    def test_engine_initialization(self):
        """Test that engine initializes correctly"""
        self.assertIsNotNone(self.engine)
        self.assertIsNotNone(self.engine.collection)
        self.assertIsNotNone(self.engine.embedding_model)
        self.assertIsNotNone(self.engine.cross_encoder)
    
    def test_collection_has_data(self):
        """Test that collection contains data"""
        count = self.engine.collection.count()
        self.assertGreater(count, 0, "Collection should have documents")
    
    def test_retrieve_candidates(self):
        """Test candidate retrieval"""
        query = "login form"
        candidates = self.engine.retrieve_candidates(query, n=10)
        
        self.assertEqual(len(candidates), 10)
        self.assertIn('content', candidates[0])
        self.assertIn('metadata', candidates[0])
    
    def test_extract_descriptions(self):
        """Test description extraction from metadata"""
        query = "login form"
        candidates = self.engine.retrieve_candidates(query, n=5)
        descriptions = self.engine.extract_descriptions(candidates)
        
        self.assertEqual(len(descriptions), 5)
        
        # Descriptions should not be empty
        for desc in descriptions:
            self.assertIsInstance(desc, str)
            self.assertGreater(len(desc), 0)
    
    def test_cross_encoder_reranking(self):
        """Test cross-encoder re-ranking"""
        query = "CSRF protection"
        candidates = self.engine.retrieve_candidates(query, n=10)
        descriptions = self.engine.extract_descriptions(candidates)
        
        reranked = self.engine.rerank_with_cross_encoder(
            query,
            candidates,
            descriptions
        )
        
        # Should have rerank scores
        self.assertIn('rerank_score', reranked[0])
        
        # Scores should be descending
        scores = [r['rerank_score'] for r in reranked]
        self.assertEqual(scores, sorted(scores, reverse=True))
    
    def test_full_query_pipeline(self):
        """Test complete query pipeline"""
        query = "How does the login form work?"
        
        results = self.engine.query(
            query_text=query,
            top_k=3,
            initial_candidates=10,
            max_snippet_chars=500
        )
        
        self.assertEqual(len(results), 3)
        
        # Each result should have required fields
        for result in results:
            self.assertIn('file_name', result)
            self.assertIn('description', result)
            self.assertIn('snippet', result)
            self.assertIn('snippet_length', result)
            
            # Snippets should be extracted
            self.assertGreater(result['snippet_length'], 0)
            self.assertLessEqual(result['snippet_length'], 700)  # With some buffer


class TestTokenReduction(unittest.TestCase):
    """Test token reduction effectiveness"""
    
    @classmethod
    def setUpClass(cls):
        """Initialize engine if available"""
        try:
            cls.engine = BladeDescriptionEngine()
            cls.engine_available = True
        except Exception:
            cls.engine_available = False
    
    def setUp(self):
        """Skip if engine not available"""
        if not self.engine_available:
            self.skipTest("Engine not available")
    
    def test_token_reduction(self):
        """Test that we achieve significant token reduction"""
        query = "login form CSRF"
        
        # Get results with snippets
        results = self.engine.query(
            query_text=query,
            top_k=5,
            initial_candidates=15,
            max_snippet_chars=1500
        )
        
        total_content = sum(r['content_length'] for r in results)
        total_snippets = sum(r['snippet_length'] for r in results)
        
        # Calculate reduction
        reduction = (total_content - total_snippets) / total_content * 100
        
        print(f"\n  Token reduction: {reduction:.1f}%")
        print(f"  Original: {total_content} chars (~{total_content//4} tokens)")
        print(f"  Snippets: {total_snippets} chars (~{total_snippets//4} tokens)")
        
        # Should achieve at least 50% reduction
        self.assertGreater(reduction, 50, "Should reduce tokens by at least 50%")
        
        # Ideally 70%+
        if reduction > 70:
            print(f"  ✅ Excellent: {reduction:.1f}% reduction!")


class TestPerformance(unittest.TestCase):
    """Test query performance"""
    
    @classmethod
    def setUpClass(cls):
        """Initialize engine if available"""
        try:
            cls.engine = BladeDescriptionEngine()
            cls.engine_available = True
        except Exception:
            cls.engine_available = False
    
    def setUp(self):
        """Skip if engine not available"""
        if not self.engine_available:
            self.skipTest("Engine not available")
    
    def test_query_speed(self):
        """Test that queries complete in reasonable time"""
        query = "user authentication"
        
        start = time.time()
        results = self.engine.query(
            query_text=query,
            top_k=5,
            initial_candidates=20
        )
        duration = time.time() - start
        
        print(f"\n  Query time: {duration:.2f}s")
        
        # Should complete in under 5 seconds
        self.assertLess(duration, 5.0, "Query should complete in under 5 seconds")
        
        # Ideally under 3 seconds
        if duration < 3.0:
            print(f"  ✅ Fast: {duration:.2f}s!")


class TestRelevance(unittest.TestCase):
    """Test retrieval relevance"""
    
    @classmethod
    def setUpClass(cls):
        """Initialize engine if available"""
        try:
            cls.engine = BladeDescriptionEngine()
            cls.engine_available = True
        except Exception:
            cls.engine_available = False
    
    def setUp(self):
        """Skip if engine not available"""
        if not self.engine_available:
            self.skipTest("Engine not available")
    
    def test_csrf_query_relevance(self):
        """Test that CSRF query returns login-related files"""
        query = "How does CSRF protection work?"
        
        results = self.engine.query(
            query_text=query,
            top_k=5,
            initial_candidates=15
        )
        
        # Top results should mention login or form
        top_files = [r['file_name'].lower() for r in results[:3]]
        top_descriptions = [r['description'].lower() for r in results[:3]]
        
        # Check if any result is relevant
        relevant = any(
            'login' in f or 'form' in f or 'csrf' in d or 'form' in d
            for f, d in zip(top_files, top_descriptions)
        )
        
        self.assertTrue(relevant, "Top results should be relevant to CSRF/forms")
    
    def test_chat_query_relevance(self):
        """Test that chat query returns chat-related files"""
        query = "Show me the chat interface"
        
        results = self.engine.query(
            query_text=query,
            top_k=3,
            initial_candidates=15
        )
        
        # Should find chat-related files
        top_files = [r['file_name'].lower() for r in results]
        
        chat_found = any('chat' in f for f in top_files)
        
        self.assertTrue(chat_found, "Should find chat-related files")


def run_tests(verbose=True):
    """Run all tests"""
    # Create test suite
    loader = unittest.TestLoader()
    suite = unittest.TestSuite()
    
    # Add test classes
    suite.addTests(loader.loadTestsFromTestCase(TestSmartSnippetExtractor))
    suite.addTests(loader.loadTestsFromTestCase(TestBladeDescriptionEngine))
    suite.addTests(loader.loadTestsFromTestCase(TestTokenReduction))
    suite.addTests(loader.loadTestsFromTestCase(TestPerformance))
    suite.addTests(loader.loadTestsFromTestCase(TestRelevance))
    
    # Run tests
    runner = unittest.TextTestRunner(verbosity=2 if verbose else 1)
    result = runner.run(suite)
    
    # Print summary
    print("\n" + "=" * 60)
    print("TEST SUMMARY")
    print("=" * 60)
    print(f"Tests run: {result.testsRun}")
    print(f"Successes: {result.testsRun - len(result.failures) - len(result.errors)}")
    print(f"Failures: {len(result.failures)}")
    print(f"Errors: {len(result.errors)}")
    print(f"Skipped: {len(result.skipped)}")
    
    if result.wasSuccessful():
        print("\n✅ All tests passed!")
    else:
        print("\n❌ Some tests failed")
    
    print("=" * 60)
    
    return result.wasSuccessful()


if __name__ == "__main__":
    print("""
╔══════════════════════════════════════════════════════════╗
║  Blade Strategy 2 - Test Suite                          ║
╚══════════════════════════════════════════════════════════╝
""")
    
    success = run_tests(verbose=True)
    sys.exit(0 if success else 1)
