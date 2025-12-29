<?php
/**
 * Integration tests for Security features actually used in the plugin
 *
 * @package EightyFourEM\LocalPages
 * @license MIT License
 * @link https://opensource.org/licenses/MIT
 */

// Load autoloader for namespaced classes
require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require_once dirname( __DIR__ ) . '/TestCase.php';

use EightyFourEM\LocalPages\Api\Encryption;
use EightyFourEM\LocalPages\Api\ApiKeyManager;
use EightyFourEM\LocalPages\Utils\ContentProcessor;

class Test_Security extends TestCase {
    
    private $encryption;
    
    /**
     * Set up test environment
     */
    public function setUp(): void {
        $this->encryption = new Encryption();
    }
    
    /**
     * Test API key encryption and decryption
     */
    public function test_api_key_encryption() {
        $apiKey = 'sk-ant-api03-test-key-123456789';
        
        // Test encryption
        $encrypted = $this->encryption->encrypt( $apiKey );
        $this->assertNotEquals( $apiKey, $encrypted );
        $this->assertStringNotContainsString( 'sk-ant', $encrypted );
        $this->assertStringNotContainsString( 'test-key', $encrypted );
        
        // Test decryption
        $decrypted = $this->encryption->decrypt( $encrypted );
        $this->assertEquals( $apiKey, $decrypted );
    }
    
    /**
     * Test that encrypted API keys are different each time (due to IV)
     */
    public function test_encryption_uniqueness() {
        $apiKey = 'sk-ant-api03-test-key-123456789';
        
        $encrypted1 = $this->encryption->encrypt( $apiKey );
        $encrypted2 = $this->encryption->encrypt( $apiKey );
        
        // Same key should produce different encrypted values due to random IV
        $this->assertNotEquals( $encrypted1, $encrypted2 );
        
        // But both should decrypt to the same value
        $this->assertEquals( $apiKey, $this->encryption->decrypt( $encrypted1 ) );
        $this->assertEquals( $apiKey, $this->encryption->decrypt( $encrypted2 ) );
    }
    
    /**
     * Test handling of empty/invalid API keys
     */
    public function test_empty_api_key_handling() {
        // Test empty string - encrypt returns false for empty
        $encrypted = $this->encryption->encrypt( '' );
        $this->assertFalse( $encrypted );
        
        // Test decryption of empty - decrypt returns false for empty
        $decrypted = $this->encryption->decrypt( '' );
        $this->assertFalse( $decrypted );
    }
    
    /**
     * Test handling of corrupted encrypted data
     */
    public function test_corrupted_encrypted_data() {
        // Test completely invalid data
        $result = $this->encryption->decrypt( 'invalid-base64-data!!!' );
        $this->assertFalse( $result );
        
        // Test truncated encrypted data
        $apiKey = 'sk-ant-api03-test-key';
        $encrypted = $this->encryption->encrypt( $apiKey );
        $corrupted = substr( $encrypted, 0, 10 );
        
        $result = $this->encryption->decrypt( $corrupted );
        $this->assertFalse( $result );
    }
    
    
    
    /**
     * Test content processor doesn't introduce XSS
     */
    public function test_content_processor_xss_prevention() {
        $processor = new ContentProcessor();

        // Test that malicious content in input doesn't create XSS
        $maliciousContent = '<!-- wp:paragraph --><p>Click here for <script>alert("xss")</script> WordPress development</p><!-- /wp:paragraph -->';

        $processed = $processor->processContent( $maliciousContent, ['type' => 'state'] );

        // Script tags should remain as-is in content (not executed)
        // The processor should not strip them but also not enhance them
        $this->assertStringContainsString( 'script', $processed );

        // But links added by processor should be properly formed
        if ( strpos( $processed, '<a href=' ) !== false ) {
            // Any links should be properly quoted
            $this->assertMatchesRegularExpression( '/<a href="[^"]+">/', $processed );
        }
    }
    
    
    
}