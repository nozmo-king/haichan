<?php
require __DIR__ . '/../vendor/autoload.php';

use Mdanter\Ecc\EccFactory;
use Mdanter\Ecc\Crypto\Signature\Signer;

if (count($argv) < 3) {
    echo "Usage: php sign-challenge.php <private_key_hex> <challenge>\n";
    exit(1);
}

$privateKeyHex = $argv[1];
$challenge = $argv[2];

try {
    $generator = EccFactory::getSecgCurves()->generator256k1();
    $signer = new Signer($generator->getAdapter());

    // Convert hex private key to GMP integer
    $privateKeyInt = gmp_init($privateKeyHex, 16);

    // Create private key object
    $privateKey = $generator->getPrivateKeyFrom($privateKeyInt);

    // Hash the challenge
    $challengeHash = hash('sha256', $challenge, true);
    $hashInt = gmp_init(bin2hex($challengeHash), 16);

    // Sign the hash
    $signature = $signer->sign($privateKey, $hashInt, gmp_random_bits(256));

    // Convert signature to hex format (r||s)
    $rHex = str_pad(gmp_strval($signature->getR(), 16), 64, '0', STR_PAD_LEFT);
    $sHex = str_pad(gmp_strval($signature->getS(), 16), 64, '0', STR_PAD_LEFT);
    $signatureHex = $rHex . $sHex;

    echo "Challenge: $challenge\n";
    echo "Challenge hash: " . bin2hex($challengeHash) . "\n";
    echo "Signature (r||s): $signatureHex\n";
    echo "R: $rHex\n";
    echo "S: $sHex\n";

} catch (Exception $e) {
    echo "Error signing challenge: " . $e->getMessage() . "\n";
    exit(1);
}
?>