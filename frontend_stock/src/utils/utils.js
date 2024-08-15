export const calculateStatistics = (quotesArray, startTime) => {
    const values = quotesArray.map((q) => q.value);
    const length = values.length;
  
    const mean = values.reduce((acc, val) => acc + val, 0) / length;
    const variance = values.reduce((acc, val) => acc + Math.pow(val - mean, 2), 0) / length;
    const stdDeviation = Math.sqrt(variance);
  
    const frequency = {};
    let mode = null;
    let maxCount = 0;
    values.forEach((val) => {
      frequency[val] = (frequency[val] || 0) + 1;
      if (frequency[val] > maxCount) {
        maxCount = frequency[val];
        mode = val;
      }
    });
  
    const min = Math.min(...values);
    const max = Math.max(...values);
  
    return {
      mean,
      stdDeviation,
      mode,
      min,
      max,
      quoteCount: length,
      elapsedTime: Date.now() - startTime,
    };
  };