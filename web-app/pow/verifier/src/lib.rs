use serde::{Deserialize, Serialize};
use sha2::{Digest, Sha256};
use std::collections::BTreeMap;

pub const MINER_VERSION: i32 = 1;

#[derive(Debug, Clone, Serialize, Deserialize, PartialEq)]
pub struct PostDraft {
    pub attachments: Vec<String>,
    pub body: String,
    #[serde(default)]
    pub refs: Vec<String>,
    pub title: String,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct CanonParams {
    pub user_pubkey_hex: String,
    pub scope: String, // 't' or 'r'
    pub thread_id: u64,
    pub parent_id: u64,
    pub timestamp_i64: i64,
    pub post_draft: PostDraft,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct VerifyParams {
    pub user_pubkey_hex: String,
    pub scope: String,
    pub thread_id: u64,
    pub parent_id: u64,
    pub timestamp_i64: i64,
    pub post_draft: PostDraft,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct TestVector {
    pub name: String,
    pub params: CanonParams,
    pub nonce: u64,
    pub required_prefix: String,
    pub expected_hash: String,
    pub should_pass: bool,
}

pub fn canonical_bytes_v1(params: CanonParams) -> Vec<u8> {
    let mut bytes = Vec::new();
    
    // prefix="HC1"
    bytes.extend_from_slice(b"HC1");
    
    // user_pubkey_hex
    bytes.extend_from_slice(params.user_pubkey_hex.as_bytes());
    
    // scope ('t' or 'r')
    bytes.extend_from_slice(params.scope.as_bytes());
    
    // thread_id as u64 little endian
    bytes.extend_from_slice(&params.thread_id.to_le_bytes());
    
    // parent_id as u64 little endian
    bytes.extend_from_slice(&params.parent_id.to_le_bytes());
    
    // timestamp_i64 as i64 little endian
    bytes.extend_from_slice(&params.timestamp_i64.to_le_bytes());
    
    // sha256(post_json_minified)
    let post_json_minified = minify_post_json(&params.post_draft);
    let post_hash = sha256_bytes(post_json_minified.as_bytes());
    bytes.extend_from_slice(&post_hash);
    
    bytes
}

fn minify_post_json(post: &PostDraft) -> String {
    // Create ordered map to ensure deterministic JSON
    let mut map = BTreeMap::new();
    map.insert("attachments", serde_json::to_value(&post.attachments).unwrap());
    map.insert("body", serde_json::to_value(&post.body).unwrap());
    map.insert("refs", serde_json::to_value(&post.refs).unwrap());
    map.insert("title", serde_json::to_value(&post.title).unwrap());
    
    serde_json::to_string(&map).unwrap()
}

pub fn sha256_hex(input: &[u8]) -> String {
    let digest = sha256_bytes(input);
    hex::encode(digest)
}

fn sha256_bytes(input: &[u8]) -> [u8; 32] {
    let mut hasher = Sha256::new();
    hasher.update(input);
    hasher.finalize().into()
}

pub fn verify_prefix(hex_digest: &str, required_prefix: &str) -> bool {
    hex_digest.starts_with(required_prefix)
}

pub fn verify_proof_v1(params: VerifyParams, nonce: u64, required_prefix: &str) -> bool {
    let canon_params = CanonParams {
        user_pubkey_hex: params.user_pubkey_hex,
        scope: params.scope,
        thread_id: params.thread_id,
        parent_id: params.parent_id,
        timestamp_i64: params.timestamp_i64,
        post_draft: params.post_draft,
    };
    
    let mut input = canonical_bytes_v1(canon_params);
    // Add extra_data="" (empty string)
    // Add nonce_u64_le
    input.extend_from_slice(&nonce.to_le_bytes());
    
    let hash_hex = sha256_hex(&input);
    verify_prefix(&hash_hex, required_prefix)
}

pub fn verify_v1(canonical_bytes: &[u8], nonce: u64, required_prefix: &str) -> (bool, String) {
    let mut input = canonical_bytes.to_vec();
    input.extend_from_slice(&nonce.to_le_bytes());
    let hash_hex = sha256_hex(&input);
    let valid = verify_prefix(&hash_hex, required_prefix);
    (valid, hash_hex)
}

#[cfg(test)]
mod tests {
    use super::*;
    use std::fs;

    #[test]
    fn test_canonical_bytes_basic() {
        let post = PostDraft {
            attachments: vec![],
            body: "test body".to_string(),
            refs: vec![],
            title: "test title".to_string(),
        };
        
        let params = CanonParams {
            user_pubkey_hex: "0x1234567890abcdef".to_string(),
            scope: "t".to_string(),
            thread_id: 0,
            parent_id: 0,
            timestamp_i64: 1640995200000,
            post_draft: post,
        };
        
        let bytes = canonical_bytes_v1(params);
        
        // Verify it starts with "HC1"
        assert_eq!(&bytes[0..3], b"HC1");
    }

    #[test]
    fn test_sha256_hex() {
        let input = b"hello world";
        let hash = sha256_hex(input);
        assert_eq!(hash, "b94d27b9934d3e08a52e52d7da7dabfac484efe37a5380ee9088f7ace2efcde9");
    }

    #[test]
    fn test_verify_prefix() {
        assert!(verify_prefix("21e8abcd", "21e8"));
        assert!(verify_prefix("0021e8abcd", "0021e8"));
        assert!(!verify_prefix("1e8abcd", "21e8"));
        assert!(!verify_prefix("21e7abcd", "21e8"));
    }

    #[test]
    fn test_json_vectors() {
        let vectors_path = "../vectors/vectors.json";
        if let Ok(contents) = fs::read_to_string(vectors_path) {
            let vectors: Vec<TestVector> = serde_json::from_str(&contents).expect("Invalid vectors JSON");
            
            for vector in vectors {
                let verify_params = VerifyParams {
                    user_pubkey_hex: vector.params.user_pubkey_hex,
                    scope: vector.params.scope,
                    thread_id: vector.params.thread_id,
                    parent_id: vector.params.parent_id,
                    timestamp_i64: vector.params.timestamp_i64,
                    post_draft: vector.params.post_draft,
                };
                
                let result = verify_proof_v1(verify_params, vector.nonce, &vector.required_prefix);
                
                if vector.should_pass {
                    assert!(result, "Vector '{}' should pass but failed", vector.name);
                } else {
                    assert!(!result, "Vector '{}' should fail but passed", vector.name);
                }
            }
        }
    }

    #[test]
    fn test_post_json_minification() {
        let post = PostDraft {
            attachments: vec!["file1.jpg".to_string(), "file2.png".to_string()],
            body: "Hello\nWorld!".to_string(),
            refs: vec!["ref1".to_string()],
            title: "My Title".to_string(),
        };
        
        let minified = minify_post_json(&post);
        
        // Parse it back to verify it's valid JSON
        let _: serde_json::Value = serde_json::from_str(&minified).unwrap();
        
        // Should contain all keys in alphabetical order
        assert!(minified.contains(r#""attachments""#));
        assert!(minified.contains(r#""body""#));
        assert!(minified.contains(r#""refs""#));
        assert!(minified.contains(r#""title""#));
    }
}