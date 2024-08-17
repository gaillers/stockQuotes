const express = require('express');
const router = express.Router();
const StatsController = require('../controllers/statsController');

router.get('/get-stats', (req, res, next) => StatsController.getAllStats(req, res, next));
router.post('/save-stats', (req, res, next) => StatsController.saveStats(req, res, next));

module.exports = router;
