import React, { useState, useRef, useCallback } from "react";
import styled from "styled-components";

import StatsDisplay from "@components/stats/StatsDisplay";
import QuoteInput from "@components/quote/QuoteInput";

import { sendStatsToBackend, fetchStatsFromBackend } from "@api/api";
import { calculateStatistics } from "@utils/utils";

const App = () => {
  const [quoteCount, setQuoteCount] = useState(100);
  const [stats, setStats] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const socketRef = useRef(null);
  const [quotes, setQuotes] = useState([]);
  const [isConnected, setIsConnected] = useState(false);
  const startTimeRef = useRef(null);
  const lastStatsRef = useRef(null);
  const isFetchingStatsRef = useRef(false);

  const handleStart = useCallback(() => {
    console.log("Starting WebSocket connection");
    
    if (socketRef.current) {
      socketRef.current.close();
    }

    setIsLoading(true);

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
            lastStatsRef.current = calculatedStats;

            if (!isFetchingStatsRef.current) {
              isFetchingStatsRef.current = true;
              sendStatsToBackend(calculatedStats)
                .catch((error) => console.error("Error sending stats:", error))
                .finally(() => {
                  isFetchingStatsRef.current = false;
                });
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
      setIsLoading(false);
    };
  }, [quoteCount]);

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

      {isLoading && <LoadingIndicator>Loading...</LoadingIndicator>}

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

const LoadingIndicator = styled.div`
  margin: 20px;
  font-size: 1.2em;
  color: #007bff;
`;
