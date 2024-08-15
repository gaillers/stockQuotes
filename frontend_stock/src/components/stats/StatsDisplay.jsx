import React from "react";
import { StatsContainer, Stat } from './StatsDisplayStyled';

const formatNumber = (num) => num.toFixed(2);

const StatsDisplay = ({ stats }) => (
  <StatsContainer>
    <h2>Statistics</h2>
    <Stat>Mean: {formatNumber(stats.mean)}</Stat>
    <Stat>Standard Deviation: {formatNumber(stats.stdDeviation)}</Stat>
    <Stat>Mode: {formatNumber(stats.mode)}</Stat>
    <Stat>Min: {formatNumber(stats.min)}</Stat>
    <Stat>Max: {formatNumber(stats.max)}</Stat>
    <Stat>Quotes Processed: {stats.quoteCount}</Stat>
    <Stat>Elapsed Time: {stats.elapsedTime} ms</Stat>
  </StatsContainer>
);

export default StatsDisplay;
