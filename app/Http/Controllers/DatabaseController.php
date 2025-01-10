<?php

namespace App\Http\Controllers;

use App\Enums\ResponseStatusCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Doctrine\DBAL\DriverManager;
use Str;

class DatabaseController extends Controller
{
    public function check(): JsonResponse
    {
        try {
            DB::connection()->getPdo();
            return $this->sendResponse(
                null,
                ResponseStatusCode::OK,
                'Connection established successfully to database: ' . DB::connection()->getDatabaseName()
            );
        } catch (\Exception $e) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::SERVICE_UNAVAILABLE,
                'Could not connect to the database. Error: ' . $e->getMessage()
            );
        }
    }

    public function create(Request $request): JsonResponse
    {
        try {
            // If custom database settings are provided, use them to test connection before database creation
            // If not, use default settings and create the database
            $isPostRequest = $request->isMethod('post');

            $host = $isPostRequest ? $request->input('host', '127.0.0.1') : env('DB_HOST', '127.0.0.1');
            $port = $isPostRequest ? $request->input('port', '3306') : env('DB_PORT', '3306');
            $username = $isPostRequest ? $request->input('username') : env('DB_USERNAME');
            $password = $isPostRequest ? $request->input('password') : env('DB_PASSWORD');
            $database = $isPostRequest ? $request->input('dbname') : env('DB_DATABASE');

            $driver = DriverManager::getConnection([
                'driver' => 'pdo_mysql',
                'host' => $host == 'localhost' ? '127.0.0.1' : $host,
                'port' => $port,
                'user' => $username,
                'password' => $password,
                'charset' => 'utf8',
                'options' => [
                    \PDO::ATTR_PERSISTENT => true, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ],
            ]);

            $driver->createSchemaManager()->createDatabase($database);

            if (!$isPostRequest) {
                return $this->sendResponse(
                    null,
                    ResponseStatusCode::OK,
                    'Connection established and database ' . $database . ' created successfully'
                );
            }
        } catch (\Exception $e) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::SERVICE_UNAVAILABLE,
                $e->getMessage()
            );
        }

        try {
            if ($request->isMethod('post')) {

                $envFilePath = base_path('.env');

                if (!File::exists($envFilePath)) {
                    // Create .env file from .env.template
                    File::copy(base_path('.env.template'), $envFilePath);
                }

                // Load the existing .env file
                $envFileContents = File::get($envFilePath);

                // Update database settings
                $databaseSettings = [
                    'DB_HOST' => $request->input('host', '127.0.0.1'),
                    'DB_PORT' => $request->input('port', '3306'),
                    'DB_DATABASE' => $request->input('dbname'),
                    'DB_USERNAME' => $request->input('username'),
                    'DB_PASSWORD' => $request->input('password'),
                ];

                foreach ($databaseSettings as $key => $value) {
                    if (Str::contains($envFileContents, $key)) {
                        // Update existing value
                        $envFileContents = preg_replace(
                            "/^$key=(.*)$/m",
                            "$key=$value",
                            $envFileContents
                        );
                    } else {
                        // Add new key-value pair
                        $envFileContents .= "\n$key=$value";
                    }
                }

                // Save the updated .env file
                File::put($envFilePath, $envFileContents);

                return $this->sendResponse(
                    null,
                    ResponseStatusCode::OK,
                    'Connection established, database ' . $request->input('dbname') . ' created successfully and .env file updated'
                );
            }
        } catch (\Exception $e) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::INTERNAL_SERVER_ERROR,
                'Could not update .env file. Error: ' . $e->getMessage()
            );
        }
    }

    public function migrate(): JsonResponse
    {
        try {
            // Run the database migrations
            Artisan::call('migrate');

            return $this->sendResponse(
                null,
                ResponseStatusCode::OK,
                'Database migrated successfully'
            );
        } catch (\Exception $e) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::INTERNAL_SERVER_ERROR,
                'Database migration failed. Error: ' . $e->getMessage()
            );
        }
    }

    public function seed(): JsonResponse
    {
        try {
            // Run the database migrations
            Artisan::call('db:seed');

            return $this->sendResponse(
                null,
                ResponseStatusCode::OK,
                'Database seeded successfully'
            );
        } catch (\Exception $e) {
            return $this->sendResponse(
                null,
                ResponseStatusCode::INTERNAL_SERVER_ERROR,
                'Database seeding failed. Error: ' . $e->getMessage()
            );
        }
    }
}
