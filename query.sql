-- SQL query 
SELECT w.id, w.name
FROM employees w
LEFT JOIN salaries z
ON w.id = z.worker_id
AND DATE_FORMAT(z.date, '%Y-%m') = '2024-08'
WHERE z.worker_id IS NULL;
