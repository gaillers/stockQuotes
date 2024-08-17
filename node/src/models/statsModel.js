const pool = require("../config/database");

class StatsModel {
  async getAllStats() {
    try {
      const [rows] = await pool.query(
        "SELECT * FROM statistics ORDER BY start_time DESC LIMIT 1"
      );

      if (rows.length === 0) {
        return {
          message: "No statistics found",
          data: null,
        };
      }

      const latestStat = rows[0];
      
      const formattedStat = {
        mean: latestStat.average,
        stdDeviation: latestStat.standard_deviation,
        mode: latestStat.mode,
        min: latestStat.min,
        max: latestStat.max,
        quoteCount: latestStat.lost_quotes,
        startTime: latestStat.start_time,
        elapsedTime: latestStat.elapsed_time,
      };

      return {
        message: "Statistics fetched successfully",
        data: formattedStat,
      };
    } catch (error) {
      throw new Error("Error fetching statistics: " + error.message);
    }
  }

  async saveStats(statistics) {
    const { mean, stdDeviation, mode, min, max, quoteCount, elapsedTime } =
      statistics;

    const parsedData = {
      average: parseFloat(mean) || null,
      stdDeviation: parseFloat(stdDeviation) || null,
      mode: parseInt(mode, 10) || null,
      min: parseInt(min, 10) || null,
      max: parseInt(max, 10) || null,
      lostQuotes: parseInt(quoteCount, 10) || null,
      elapsedTime: parseInt(elapsedTime, 10) || null,
      startTime: new Date().toISOString().slice(0, 19).replace("T", " "),
    };

    try {
      await pool.query(
        `INSERT INTO statistics 
        (average, standard_deviation, mode, min, max, lost_quotes, start_time, elapsed_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          parsedData.average,
          parsedData.stdDeviation,
          parsedData.mode,
          parsedData.min,
          parsedData.max,
          parsedData.lostQuotes,
          parsedData.startTime,
          parsedData.elapsedTime,
        ]
      );

      return { message: "Statistics saved successfully", data: statistics };
    } catch (error) {
      throw new Error("Error saving statistics: " + error.message);
    }
  }
}

module.exports = new StatsModel();
