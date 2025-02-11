/// <reference types="Cypress" />

//Checking the console for errors
let windowConsoleError;
Cypress.on('window:before:load', (win) => {
  windowConsoleError = cy.spy(win.console, 'error');
})
let failEarly = false;
afterEach(() => {
  expect(windowConsoleError).to.not.be.called;
  if (failEarly) throw new Error("Failing Early due to an API or other module configuration problem. If running on CI, please check Cypress VIDEOS/SCREENSHOTS in the Artifacts for more details.")
})
afterEach(function() {
  if (this.currentTest.state === "failed") failEarly = true
});
describe('PS8 Module initial configuration setup', () => {
  beforeEach(() => {
      cy.viewport(1920,1080)
      cy.CachingBOFOPS8()
  })
it('C339305: Connecting test API successsfully', () => {
      cy.visit('/admin1/')
      cy.get('.mi-mollie').click({fore:true})
      cy.get('#subtab-AdminMollieModule').click()
      cy.get('body')
      .invoke('text').should('contain','Mollie')
      .then((text) => {
      cy.log(text) // Showing and asserting the text that loaded, to ensure the BO is loaded, not crashed with PHP fatals etc.
      })
      // cy.iframe('[id^="uid_"]').find('button').click() // Cloudsync validation
      // cy.wait(15000) // Waiting for validation to process until the end
      cy.get('#MOLLIE_ACCOUNT_SWITCH_on').click({force:true})
      cy.get('#MOLLIE_API_KEY_TEST').type((Cypress.env('MOLLIE_TEST_API_KEY')),{delay: 0, log: false})
      cy.get('#module_form_submit_btn').click()
})
it('C339338: Enabling Mollie carriers in Prestashop successfully', () => {
      cy.visit('/admin1/')
      cy.get('[id="subtab-AdminPaymentPreferences"]').find('[href]').eq(0).click({force:true})
      cy.get('[class="js-multiple-choice-table-select-column"]').each(($element) => { // checks all the checkboxes in the loop, if not checked
        cy.wrap($element).click()
      })
      cy.get('[id="form-carrier-restrictions-save-button"]').click()
})
it('C339339: Checking the Advanced Settings tab, verifying the Front-end components, Saving the form, checking if there are no Errors in Console', () => {
      cy.OpeningModuleDashboardURL()
      cy.get('[href="#advanced_settings"]').click({force:true})
      cy.advancedSettingsValidation()
      cy.reload()
      cy.wait(3000)
      cy.matchImage(); // let's make a snapshot for visual regression testing later, if UI matches
});
it('C688472: Checking the Subscriptions tab, and console errors', () => {
      cy.OpeningModuleDashboardURL()
      cy.get('#subtab-AdminMollieSubscriptionOrders').click()
      cy.get('[id="invertus_mollie_subscription_grid_panel"]').should('be.visible')
      cy.selectSubscriptionsCarriersCheck() // checking the Subscriptions carriers select and saving
});
it('C688473: Checking the Subscriptions FAQ, and console errors', () => {
      cy.OpeningModuleDashboardURL()
      cy.get('#subtab-AdminMollieSubscriptionFAQ').click()
      cy.subscriptionsUiCheck()
      cy.wait(3000)
      cy.matchImage(); // let's make a snapshot for visual regression testing later, if UI matches
});
})
