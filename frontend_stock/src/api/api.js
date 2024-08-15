export const sendStatsToBackend = async (stats) => {
  try {
    await fetch("http://localhost-backend/save-stats", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(stats),
    });
  } catch (error) {
    console.error("Error sending stats to backend:", error);
  }
};

export const fetchStatsFromBackend = async (setStats) => {
    try {
      const response = await fetch("http://localhost-backend/get-stats", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      });
      const result = await response.json();
      setStats(result.data);
    } catch (error) {
      console.error("Error fetching stats:", error);
    }
  };
