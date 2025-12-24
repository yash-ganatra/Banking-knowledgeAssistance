"""
Network Diagnostics Script for BookStack Server
"""
import socket
import subprocess
import sys
import requests
from urllib.parse import urlparse

SERVER_URL = 'http://143.244.141.254:8008/'
API_URL = f'{SERVER_URL}api/docs'


def test_dns_resolution(hostname):
    """Test if hostname can be resolved"""
    print(f"\n1. DNS Resolution Test for {hostname}")
    print("-" * 70)
    try:
        ip = socket.gethostbyname(hostname)
        print(f"   ✓ Resolved to: {ip}")
        return True
    except socket.gaierror as e:
        print(f"   ✗ Failed to resolve: {e}")
        return False


def test_ping(host):
    """Test if host responds to ping"""
    print(f"\n2. Ping Test to {host}")
    print("-" * 70)
    try:
        # macOS/Linux ping command
        result = subprocess.run(
            ['ping', '-c', '3', '-W', '5', host],
            capture_output=True,
            text=True,
            timeout=15
        )
        
        if result.returncode == 0:
            print(f"   ✓ Host is reachable via ping")
            # Extract relevant info
            lines = result.stdout.split('\n')
            for line in lines:
                if 'time=' in line or 'packets transmitted' in line or 'rtt' in line:
                    print(f"   {line.strip()}")
            return True
        else:
            print(f"   ✗ Ping failed (exit code: {result.returncode})")
            print(f"   {result.stderr}")
            return False
    except subprocess.TimeoutExpired:
        print(f"   ✗ Ping timed out")
        return False
    except Exception as e:
        print(f"   ✗ Ping test error: {e}")
        return False


def test_port_connectivity(host, port, timeout=10):
    """Test if specific port is open"""
    print(f"\n3. Port Connectivity Test to {host}:{port}")
    print("-" * 70)
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout)
        result = sock.connect_ex((host, port))
        sock.close()
        
        if result == 0:
            print(f"   ✓ Port {port} is OPEN and accepting connections")
            return True
        else:
            print(f"   ✗ Port {port} is CLOSED or filtered (Error code: {result})")
            print(f"   Error codes: 61=Connection refused, 60=Timeout, 51=Network unreachable")
            return False
    except socket.timeout:
        print(f"   ✗ Connection timed out after {timeout}s")
        return False
    except Exception as e:
        print(f"   ✗ Connection test failed: {e}")
        return False


def test_http_request(url, timeout=15):
    """Test HTTP request to the server"""
    print(f"\n4. HTTP Request Test to {url}")
    print("-" * 70)
    try:
        print(f"   Attempting GET request (timeout: {timeout}s)...")
        response = requests.get(url, timeout=timeout, allow_redirects=True)
        
        print(f"   ✓ Server responded!")
        print(f"   Status Code: {response.status_code}")
        print(f"   Response Time: {response.elapsed.total_seconds():.2f}s")
        print(f"   Content Length: {len(response.content)} bytes")
        
        if response.status_code == 200:
            print(f"   ✓ Server is accessible via HTTP")
            return True
        else:
            print(f"   ⚠ Unusual status code: {response.status_code}")
            return False
            
    except requests.exceptions.ConnectTimeout:
        print(f"   ✗ Connection timed out after {timeout}s")
        return False
    except requests.exceptions.ConnectionError as e:
        print(f"   ✗ Connection error: {e}")
        return False
    except requests.exceptions.Timeout:
        print(f"   ✗ Request timed out")
        return False
    except Exception as e:
        print(f"   ✗ HTTP request failed: {e}")
        return False


def test_api_endpoint(url, token_id, token_secret):
    """Test BookStack API endpoint"""
    print(f"\n5. BookStack API Authentication Test")
    print("-" * 70)
    try:
        api_url = url.rstrip('/') + '/api/docs'
        headers = {
            'Authorization': f'Token {token_id}:{token_secret}',
            'Accept': 'application/json'
        }
        
        print(f"   Testing: {api_url}")
        response = requests.get(api_url, headers=headers, timeout=15)
        
        print(f"   Status Code: {response.status_code}")
        
        if response.status_code == 200:
            print(f"   ✓ API authentication successful!")
            return True
        elif response.status_code == 401:
            print(f"   ✗ Authentication failed - check your token credentials")
            return False
        else:
            print(f"   ⚠ Unexpected response: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"   ✗ API test failed: {e}")
        return False


def check_network_interface():
    """Check network interface information"""
    print(f"\n6. Local Network Configuration")
    print("-" * 70)
    try:
        hostname = socket.gethostname()
        local_ip = socket.gethostbyname(hostname)
        print(f"   Hostname: {hostname}")
        print(f"   Local IP: {local_ip}")
        return True
    except Exception as e:
        print(f"   ✗ Failed to get network info: {e}")
        return False


def main():
    """Run all diagnostic tests"""
    print("=" * 70)
    print("BookStack Server Connection Diagnostics")
    print("=" * 70)
    
    # Parse URL
    parsed = urlparse(SERVER_URL)
    host = parsed.hostname
    port = parsed.port or 80
    
    print(f"\nTarget Server: {SERVER_URL}")
    print(f"Host: {host}")
    print(f"Port: {port}")
    
    # Run tests
    results = []
    
    # Test 1: DNS Resolution
    results.append(("DNS Resolution", test_dns_resolution(host)))
    
    # Test 2: Ping
    results.append(("Ping", test_ping(host)))
    
    # Test 3: Port Connectivity
    results.append(("Port Connectivity", test_port_connectivity(host, port, timeout=10)))
    
    # Test 4: HTTP Request
    results.append(("HTTP Request", test_http_request(SERVER_URL, timeout=15)))
    
    # Test 5: API Authentication (only if HTTP works)
    if results[-1][1]:  # If HTTP request worked
        from bookstack_api import BookStackConfig
        config = BookStackConfig(
            base_url=SERVER_URL,
            token_id='4ZQrO9Uc1LnHOLfKc4RsNG0RwE1JCPdJ',
            token_secret='bgAM9sNZzt5iI8voZCNkxLQrQHipTMkc'
        )
        results.append(("API Authentication", 
                       test_api_endpoint(SERVER_URL, config.token_id, config.token_secret)))
    
    # Test 6: Network Interface
    results.append(("Network Configuration", check_network_interface()))
    
    # Summary
    print("\n" + "=" * 70)
    print("DIAGNOSTIC SUMMARY")
    print("=" * 70)
    
    for test_name, passed in results:
        status = "✓ PASS" if passed else "✗ FAIL"
        print(f"{status:8} | {test_name}")
    
    passed_count = sum(1 for _, passed in results if passed)
    total_count = len(results)
    
    print("-" * 70)
    print(f"Total: {passed_count}/{total_count} tests passed")
    print("=" * 70)
    
    # Recommendations
    print("\nRECOMMENDATIONS:")
    
    if not results[0][1]:  # DNS failed
        print("❌ DNS resolution failed - check hostname")
    
    if not results[1][1]:  # Ping failed
        print("❌ Server not responding to ping - may be down or blocking ICMP")
    
    if not results[2][1]:  # Port connectivity failed
        print("❌ Cannot connect to port - check:")
        print("   • Is the BookStack server running?")
        print("   • Are you on the correct network/VPN?")
        print("   • Is there a firewall blocking the connection?")
        print("   • Is the IP address and port correct?")
    
    if results[2][1] and not results[3][1]:  # Port open but HTTP failed
        print("⚠️  Port is open but HTTP request failed")
        print("   • The server might not be running HTTP on this port")
        print("   • Try accessing the URL in a web browser")
    
    if passed_count == total_count:
        print("✅ All tests passed! The server is accessible.")
    
    print("\nNext steps:")
    print("1. Try accessing the URL in your browser: " + SERVER_URL)
    print("2. Verify you're on the correct network/VPN")
    print("3. Contact your network administrator if issues persist")
    print("=" * 70)


if __name__ == "__main__":
    main()
