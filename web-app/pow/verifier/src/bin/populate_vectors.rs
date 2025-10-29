use verifier::{canonical_bytes_v1, verify_v1, PostDraft, CanonParams};
use serde::{Deserialize, Serialize};
use std::fs;

#[derive(Serialize, Deserialize)]
struct TestVector {
    name: String,
    description: String,
    inputs: serde_json::Value,
    expected_nonce: Option<u64>,
    expected_hash_hex: Option<String>,
    expected_valid: bool,
    #[serde(skip_serializing_if = "Option::is_none")]
    reject_reason: Option<String>,
    #[serde(skip_serializing_if = "Option::is_none")]
    notes: Option<String>,
}

#[derive(Serialize, Deserialize)]
struct VectorFile {
    version: i32,
    description: String,
    vectors: Vec<TestVector>,
}

fn main() {
    let content = fs::read_to_string("../vectors/v1_test_vectors.json")
        .expect("Failed to read test vectors");
    
    let mut vector_file: VectorFile = serde_json::from_str(&content)
        .expect("Failed to parse JSON");

    for vector in &mut vector_file.vectors {
        if vector.name == "solvable_21e8_thread" || vector.name == "solvable_0021e8_reply" {
            let inputs = &vector.inputs;
            let user_pubkey_hex = inputs["user_pubkey_hex"].as_str().unwrap().to_string();
            let scope_str = inputs["scope"].as_str().unwrap();
            let scope = if scope_str == "thread" { "t" } else { "r" }.to_string();
            let thread_id = inputs["thread_id"].as_u64().unwrap();
            let parent_id = inputs["parent_id"].as_u64().unwrap();
            let timestamp_i64 = inputs["timestamp_i64"].as_i64().unwrap();
            let required_prefix_hex = inputs["required_prefix_hex"].as_str().unwrap();
            
            let post_draft = PostDraft {
                attachments: inputs["post_draft"]["attachments"]
                    .as_array()
                    .unwrap()
                    .iter()
                    .map(|v| v.as_str().unwrap().to_string())
                    .collect(),
                body: inputs["post_draft"]["body"].as_str().unwrap().to_string(),
                refs: inputs["post_draft"]["refs"]
                    .as_array()
                    .unwrap()
                    .iter()
                    .map(|v| v.as_str().unwrap().to_string())
                    .collect(),
                title: inputs["post_draft"]["title"].as_str().unwrap().to_string(),
            };

            let params = CanonParams {
                user_pubkey_hex,
                scope,
                thread_id,
                parent_id,
                timestamp_i64,
                post_draft,
            };

            let canonical_bytes = canonical_bytes_v1(params);

            let max_iters = if vector.name.contains("0021e8") { 10_000_000 } else { 1_000_000 };
            
            println!("Mining {} with prefix {}...", vector.name, required_prefix_hex);
            for nonce in 0..max_iters {
                let (valid, hash) = verify_v1(&canonical_bytes, nonce, required_prefix_hex);
                if valid {
                    println!("  Found nonce: {} hash: {}", nonce, hash);
                    vector.expected_nonce = Some(nonce);
                    vector.expected_hash_hex = Some(hash);
                    break;
                }
                if nonce % 100000 == 0 && nonce > 0 {
                    print!(".");
                    if nonce % 1000000 == 0 {
                        println!(" {}", nonce);
                    }
                }
            }
            println!();
        }
    }

    let output = serde_json::to_string_pretty(&vector_file).unwrap();
    fs::write("../vectors/v1_test_vectors_solved.json", &output)
        .expect("Failed to write output");
    
    println!("\nTest vectors saved to vectors/v1_test_vectors_solved.json");
}

