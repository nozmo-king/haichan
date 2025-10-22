
use sha2::{Digest, Sha256};
use serde::{Deserialize, Serialize};

pub const V1_PREFIX: &[u8] = b"HC1";

#[derive(Serialize, Deserialize, Debug, Clone)]
pub struct PostDraft {
    pub title: String,
    pub body: String,
    pub attachments: Vec<String>,
    pub refs: Vec<String>,
}

#[derive(Debug, Clone)]
pub enum Scope {
    Thread,
    Reply,
}

impl Scope {
    pub fn as_bytes(&self) -> &[u8] {
        match self {
            Scope::Thread => b"t",
            Scope::Reply => b"r",
        }
    }
}

pub fn canonical_bytes_v1(
    user_pubkey_hex: &str,
    scope: Scope,
    thread_id: u64,
    parent_id: u64,
    timestamp_i64: i64,
    post_draft: &PostDraft,
) -> Vec<u8> {
    let post_json_minified = serde_json::to_string(post_draft).unwrap();
    let post_hash = Sha256::digest(post_json_minified.as_bytes());

    let mut bytes = Vec::new();
    bytes.extend_from_slice(V1_PREFIX);
    bytes.extend_from_slice(user_pubkey_hex.as_bytes());
    bytes.extend_from_slice(scope.as_bytes());
    bytes.extend_from_slice(&thread_id.to_le_bytes());
    bytes.extend_from_slice(&parent_id.to_le_bytes());
    bytes.extend_from_slice(&timestamp_i64.to_le_bytes());
    bytes.extend_from_slice(&post_hash);

    bytes
}

pub fn verify_v1(
    input_bytes: &[u8],
    required_prefix_hex: &str,
) -> (bool, String) {
    let digest = Sha256::digest(input_bytes);
    let digest_hex = hex::encode(digest);
    (digest_hex.starts_with(required_prefix_hex), digest_hex)
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_canonical_bytes_v1() {
        let post_draft = PostDraft {
            title: "test title".to_string(),
            body: "test body".to_string(),
            attachments: vec![],
            refs: vec![],
        };
        let user_pubkey_hex = "02c9d9c3f5a6f3a9e1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5";
        let scope = Scope::Thread;
        let thread_id = 0;
        let parent_id = 0;
        let timestamp_i64 = 1678886400;

        let bytes = canonical_bytes_v1(
            user_pubkey_hex,
            scope,
            thread_id,
            parent_id,
            timestamp_i64,
            &post_draft,
        );

        let post_json_minified = serde_json::to_string(&post_draft).unwrap();
        let post_hash = Sha256::digest(post_json_minified.as_bytes());

        let mut expected_bytes = Vec::new();
        expected_bytes.extend_from_slice(V1_PREFIX);
        expected_bytes.extend_from_slice(user_pubkey_hex.as_bytes());
        expected_bytes.extend_from_slice(b"t");
        expected_bytes.extend_from_slice(&0u64.to_le_bytes());
        expected_bytes.extend_from_slice(&0u64.to_le_bytes());
        expected_bytes.extend_from_slice(&1678886400i64.to_le_bytes());
        expected_bytes.extend_from_slice(&post_hash);

        assert_eq!(bytes, expected_bytes);
    }

    #[test]
    fn test_verify_v1_positive() {
        let nonce: u64 = 21;
        let mut input_bytes = "test input".as_bytes().to_vec();
        input_bytes.extend_from_slice(&nonce.to_le_bytes());

        let (is_valid, digest_hex) = verify_v1(&input_bytes, "21e8");
        assert!(is_valid);
        assert!(digest_hex.starts_with("21e8"));
    }

    #[test]
    fn test_verify_v1_negative() {
        let nonce: u64 = 0;
        let mut input_bytes = "test input".as_bytes().to_vec();
        input_bytes.extend_from_slice(&nonce.to_le_bytes());
        let (is_valid, _) = verify_v1(&input_bytes, "0000");
        assert!(!is_valid);
    }
}
