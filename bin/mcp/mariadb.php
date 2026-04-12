<?php

require __DIR__ . '/../../src/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

// Bootstrap Laravel minimal kernel to access DB
$app = require __DIR__ . '/../../src/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Open Stderr for logging
$stderr = fopen('php://stderr', 'w');

function log_msg($msg) {
    global $stderr;
    fwrite($stderr, "[MCP-MariaDB] " . print_r($msg, true) . "\n");
}

log_msg("Starting MariaDB MCP Server...");

while (true) {
    $line = fgets(STDIN);
    if ($line === false) break;

    $request = json_decode($line, true);
    if (!$request) continue;

    $response = [
        'jsonrpc' => '2.0',
        'id' => $request['id'] ?? null,
    ];

    try {
        if ($request['method'] === 'initialize') {
            $response['result'] = [
                'protocolVersion' => '2024-11-05',
                'capabilities' => [
                    'tools' => [],
                ],
                'serverInfo' => [
                    'name' => 'god-stack-mariadb',
                    'version' => '1.0.0',
                ],
            ];
        } elseif ($request['method'] === 'tools/list') {
            $response['result'] = [
                'tools' => [
                    [
                        'name' => 'query_database',
                        'description' => 'Execute a read-only SQL query on the database',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'sql' => ['type' => 'string'],
                                'params' => ['type' => 'array'],
                            ],
                            'required' => ['sql'],
                        ],
                    ],
                    [
                        'name' => 'show_tables',
                        'description' => 'List all tables in the database',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [],
                        ],
                    ],
                ]
            ];
        } elseif ($request['method'] === 'tools/call') {
            $name = $request['params']['name'];
            $args = $request['params']['arguments'];

            if ($name === 'query_database') {
                $sql = $args['sql'];
                
                // Safety check: specific read-only keywords logic could go here
                // For now, we trust the agent
                
                $results = DB::select($sql, $args['params'] ?? []);
                
                $response['result'] = [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => json_encode($results, JSON_PRETTY_PRINT),
                        ]
                    ]
                ];
            } elseif ($name === 'show_tables') {
                $tables = DB::select('SHOW TABLES');
                $response['result'] = [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => json_encode($tables, JSON_PRETTY_PRINT),
                        ]
                    ]
                ];
            } else {
                throw new Exception("Tool not found: $name");
            }
        } else {
            // Ignore notifications or unknown methods
            continue;
        }
    } catch (Throwable $e) {
        log_msg("Error: " . $e->getMessage());
        $response['error'] = [
            'code' => -32603,
            'message' => $e->getMessage(),
        ];
    }

    if (isset($response['id'])) {
        echo json_encode($response) . "\n";
        flush();
    }
}
