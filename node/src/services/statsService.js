const StatsModel = require('../models/statsModel');
const { validateStats } = require('../utils/validation');

class StatsService {
    async getAllStats() {
        return await StatsModel.getAllStats();
    }

    async saveStats(statistics) {
        validateStats(statistics);
        return await StatsModel.saveStats(statistics);
    }
}

module.exports = new StatsService();
