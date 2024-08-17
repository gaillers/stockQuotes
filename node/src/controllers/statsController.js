const StatsService = require('../services/statsService');
const logger = require('../config/logger');

class StatsController {
    async getAllStats(req, res, next) {
        try {
            const statistics = await StatsService.getAllStats();
            res.json(statistics);
        } catch (error) {
            logger.error('Error fetching statistics: ' + error.message);
            next(error);
        }
    }

    async saveStats(req, res, next) {
        try {
            const statistics = await StatsService.saveStats(req.body);
            res.json(statistics);
        } catch (error) {
            logger.error('Error saving statistics: ' + error.message);
            next(error);
        }
    }
}

module.exports = new StatsController();
