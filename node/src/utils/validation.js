function validateStats(statistics) {
    if (typeof statistics.mean !== 'number' || isNaN(statistics.mean)) {
        throw new Error('Validation failed: Invalid average value');
    }
    if (typeof statistics.stdDeviation !== 'number' || isNaN(statistics.stdDeviation)) {
        throw new Error('Validation failed: Invalid standard deviation value');
    }
    if (typeof statistics.mode !== 'number' || isNaN(statistics.mode)) {
        throw new Error('Validation failed: Invalid mode value');
    }
    if (typeof statistics.min !== 'number' || isNaN(statistics.min)) {
        throw new Error('Validation failed: Invalid min value');
    }
    if (typeof statistics.max !== 'number' || isNaN(statistics.max)) {
        throw new Error('Validation failed: Invalid max value');
    }
    if (typeof statistics.quoteCount !== 'number' || isNaN(statistics.quoteCount)) {
        throw new Error('Validation failed: Invalid quote count value');
    }
    if (typeof statistics.elapsedTime !== 'number' || isNaN(statistics.elapsedTime)) {
        throw new Error('Validation failed: Invalid elapsed time value');
    }
}

module.exports = {
    validateStats,
};
