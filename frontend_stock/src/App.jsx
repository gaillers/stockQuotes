import React, { useState, useRef, useCallback } from "react";
import styled from "styled-components";

import StatsDisplay from "@components/stats/StatsDisplay";
import QuoteInput from "@components/quote/QuoteInput";

import { sendStatsToBackend, fetchStatsFromBackend } from "@api/api";
import { calculateStatistics } from "@utils/utils";

const App = () => {
  const [quoteCount, setQuoteCount] = useState(100);
  const [stats, setStats] = useState(null);
  const socketRef = useRef(null);
  const [quotes, setQuotes] = useState([]);
  const [isConnected, setIsConnected] = useState(false);
  const startTimeRef = useRef(null);
  const [isSendingStats, setIsSendingStats] = useState(false);
  const lastStatsRef = useRef(null);

  const handleStart = useCallback(() => {
    console.log("Starting WebSocket connection");
    if (socketRef.current) {
      socketRef.current.close();
    }

    startTimeRef.current = Date.now();
    socketRef.current = new WebSocket(
      "wss://trade.termplat.com:8800/?password=1234"
    );

    socketRef.current.onopen = () => {
      console.log("Connected to WebSocket");
      setIsConnected(true);
    };

    socketRef.current.onmessage = (event) => {
      const data = JSON.parse(event.data);
      setQuotes((prevQuotes) => {
        const updatedQuotes = [...prevQuotes, data];
        if (updatedQuotes.length >= quoteCount) {
          const calculatedStats = calculateStatistics(
            updatedQuotes,
            startTimeRef.current
          );
          if (
            JSON.stringify(calculatedStats) !==
            JSON.stringify(lastStatsRef.current)
          ) {
            setStats(calculatedStats);
            if (!isSendingStats) {
              setIsSendingStats(true);
              sendStatsToBackend(calculatedStats).finally(() =>
                setIsSendingStats(false)
              );
              lastStatsRef.current = calculatedStats;
            }
          }
          socketRef.current.close();
        }
        return updatedQuotes;
      });
    };

    socketRef.current.onerror = (error) => {
      console.error("WebSocket Error:", error);
    };

    socketRef.current.onclose = () => {
      console.log("WebSocket connection closed");
      setIsConnected(false);
    };
  }, [quoteCount, isSendingStats]);

  const handleQuoteCountChange = (e) => {
    setQuoteCount(Number(e.target.value));
  };

  const handleShowStats = async () => {
    await fetchStatsFromBackend(setStats);
  };

  return (
    <AppContainer>
      <h1>Stock Quotes Statistics</h1>
      <QuoteInput value={quoteCount} onChange={handleQuoteCountChange} />
      <Button onClick={handleStart} disabled={isConnected}>
        Start
      </Button>
      <Button onClick={handleShowStats}>Show Stats</Button>

      {stats && <StatsDisplay stats={stats} />}
    </AppContainer>
  );
};

export default App;
const AppContainer = styled.div`
  font-family: Arial, sans-serif;
  text-align: center;
  padding: 20px;
`;

const Button = styled.button`
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  margin: 10px;
  cursor: pointer;
  border-radius: 5px;

  &:disabled {
    background-color: #c0c0c0;
  }
`;
