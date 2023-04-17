const { defineConfig } = require('cypress')

module.exports = defineConfig({
  chromeWebSecurity: false,
  experimentalSourceRewriting: true,
  numTestsKeptInMemory: 2,
  defaultCommandTimeout: 8000,
  projectId: 'xb89dr',
  retries: 2,
  videoUploadOnPasses: false,
  videoCompression: 8,
  viewportHeight: 1080,
  viewportWidth: 1920,
  experimentalMemoryManagement: true,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config)
    },
    setupNodeEvents(on, config) {
      require("cypress-fail-fast/plugin")(on, config);
      return config;
    },
    excludeSpecPattern: ['index.php'],
    specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
  },
})
