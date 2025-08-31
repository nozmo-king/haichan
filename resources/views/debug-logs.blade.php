@extends('layout')

@section('title', 'Debug Logs')

@section('content')
<h2>Client-Side Debug Logs</h2>

<button onclick="showLogs()" style="padding: 8px 16px; margin: 10px 0; background: #007bff; color: white; border: none; border-radius: 4px;">Show Logs</button>
<button onclick="clearLogs()" style="padding: 8px 16px; margin: 10px 0; background: #dc3545; color: white; border: none; border-radius: 4px;">Clear Logs</button>

<pre id="logs" style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; max-height: 500px; overflow-y: auto;"></pre>

<script>
function showLogs() {
    const logs = JSON.parse(localStorage.getItem('threadCreateLogs') || '[]');
    const logsElement = document.getElementById('logs');
    
    if (logs.length === 0) {
        logsElement.textContent = 'No logs found';
        return;
    }
    
    logsElement.textContent = logs.map(log => 
        `[${log.timestamp}] ${log.message}\n${JSON.stringify(log.data, null, 2)}\n---`
    ).join('\n');
}

function clearLogs() {
    localStorage.removeItem('threadCreateLogs');
    document.getElementById('logs').textContent = 'Logs cleared';
}

// Auto-show logs on page load
window.addEventListener('DOMContentLoaded', showLogs);
</script>
@endsection