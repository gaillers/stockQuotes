<?php

class StatsController
{
    public static function getStats()
    {
      
        $pdo = self::getDatabaseConnection();

        try {
            $sql = 'SELECT * FROM statistics ORDER BY id DESC LIMIT 1';
            $stmt = $pdo->query($sql);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stats) {
                throw new Exception('No statistics found');
            }

            $response = [
                'message' => 'Last statistics retrieved successfully',
                'data' => [
                    'mean' => (float)$stats['average'],
                    'stdDeviation' => (float)$stats['standard_deviation'],
                    'mode' => (int)$stats['mode'],
                    'min' => (int)$stats['min'],
                    'max' => (int)$stats['max'],
                    'quoteCount' => (int)$stats['lost_quotes'],
                    'elapsedTime' => (int)$stats['elapsed_time'],
                ],
            ];

            header('Content-Type: application/json');
            http_response_code(200);

            echo json_encode($response);
        } catch (Exception $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['reason' => 'Server Error', 'message' => 'An error occurred while fetching statistics.']);
        }
    }

    public static function saveStats($data)
    {
        $requiredFields = ['mean', 'stdDeviation', 'mode', 'min', 'max', 'quoteCount', 'elapsedTime'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                header('Content-Type: application/json', true, 400);
                echo json_encode(['reason' => 'Bad Request', 'message' => "Missing required parameter: $field"]);

                exit;
            }
        }

        $pdo = self::getDatabaseConnection();

        $sql = 'INSERT INTO statistics (average, standard_deviation, mode, min, max, lost_quotes, start_time, elapsed_time)
                VALUES (:average, :standard_deviation, :mode, :min, :max, :lost_quotes, :start_time, :elapsed_time)';
        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            ':average' => (float)$data['mean'], 
            ':standard_deviation' => (float)$data['stdDeviation'],
            ':mode' => (int)$data['mode'],
            ':min' => (int)$data['min'],
            ':max' => (int)$data['max'],
            ':lost_quotes' => (int)$data['quoteCount'],
            ':start_time' => date('Y-m-d H:i:s'), 
            ':elapsed_time' => (int)$data['elapsedTime']
        ]);
        
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode(['message' => 'Statistics saved successfully']);
    }

    private static function getDatabaseConnection()
    {
        $dbHost = JDotEnv::get('DB_HOST', null, 'DB');
        $dbUser = JDotEnv::get('DB_USER', null, 'DB');
        $dbBase = JDotEnv::get('DB_BASE', null, 'DB');
        $dbPass = JDotEnv::get('DB_PASS', null, 'DB');

        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbBase;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['reason' => 'Server Error', 'message' => 'Database connection error: ' . $e->getMessage()]);
            exit;
        }
    }
}
