export const sendStatsToBackend = async (stats) => {
  try {
    const response = await fetch("http://localhost-backend/save-stats", { // PHP
    // const response = await fetch("http://localhost:3030/save-stats", {  // NodeJS
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(stats),
    });

    await response.json();
  } catch (error) {
    console.error("Error sending stats to backend:", error);
  }
};

export const fetchStatsFromBackend = async (setStats) => {
  try {
    const response = await fetch("http://localhost-backend/get-stats", { // PHP
    // const response = await fetch("http://localhost:3030/get-stats", { // NodeJS
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Network response was not ok");
    }
    
    const result = await response.json();
    setStats(result.data);
  } catch (error) {
    console.error("Error fetching stats:", error);
  }
};
