#!/bin/bash
echo "=== Monitoring Mining Requests ==="
echo "Watching: storage/logs/laravel.log"
echo "Press Ctrl+C to stop"
echo
tail -f storage/logs/laravel.log | grep --line-buffered -i "challenge\|mining\|hash\|proof" | grep --line-buffered -v "route:list"
