const express = require("express");
const bodyParser = require("body-parser");
const cors = require("cors");

const logger = require("../src/config/logger");

const errorHandler = require("../src/utils/errorHandler");

const { PORT } = require("../src/config/env");

const app = express();

app.use(cors());

app.use(bodyParser.json());

app.use((req, res, next) => {
  logger.info(`Request: ${req.method} ${req.url}`);
  next();
});

app.use("/", require("../src/routes/statsRoutes"));

app.use(errorHandler);

app.listen(PORT, () => {
  logger.info(`Server is running on port ${PORT}`);
});
