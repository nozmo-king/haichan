<?php

namespace App\Services;

use FFI;

class PowVerifierFFI
{
    protected FFI $ffi;

    public function __construct()
    {
        // Adjust the path to the compiled Rust library based on your OS
        // For Linux: libverifier.so
        // For macOS: libverifier.dylib
        // For Windows: verifier.dll
        $libPath = __DIR__ . "/../../pow/verifier/target/release/libverifier.so";

        if (!file_exists($libPath)) {
            throw new \Exception("Rust verifier library not found at: " . $libPath);
        }

        $this->ffi = FFI::cdef(
            "bool verify_pow_v1(const unsigned char* input_bytes, size_t input_bytes_len, const char* required_prefix_hex);",
            $libPath
        );
    }

    public function verifyPowV1(string $inputBytes, string $requiredPrefixHex): bool
    {
        $inputBytesLen = strlen($inputBytes);
        $inputBytesPtr = FFI::new("unsigned char[" . $inputBytesLen . "]", false);
        FFI::memcpy($inputBytesPtr, $inputBytes, $inputBytesLen);

        return $this->ffi->verify_pow_v1(
            $inputBytesPtr,
            $inputBytesLen,
            $requiredPrefixHex
        );
    }
}
