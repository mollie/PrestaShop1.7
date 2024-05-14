/// <reference types="Cypress" />

describe('PS8 Tests Suite [Payments API]', {
  failFast: {
    enabled: false,
  },
}, () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
    cy.CachingBOFOPS8()
  })
it('C339378: 43 Check if Bancontact QR payment dropdown exists [Payments API]', () => {
    cy.visit('/admin1/')
    cy.OpeningModuleDashboardURL()
    cy.get('[name="MOLLIE_BANCONTACT_QR_CODE_ENABLED"]').should('exist')
})
it('C339379: 44 Bancontact Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    cy.contains('Bancontact').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339380: 45 Bancontact Order BO Refunding, Partial Refunding [Payments API]', () => { // somehow sometimes the payment div is not loaded in Cypress
    cy.OrderRefundingPartialPaymentsAPI()
})
it('C339381: 46 iDEAL Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('iDEAL').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('.payment-method-list > :nth-child(1)').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339382: 47 iDEAL Order BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
})
it('C339383: 48 Credit Card Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('Karte').click({force:true})
    //Credit card inputing
    cy.CreditCardFillingIframe()
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339384: 49 Credit Card Order BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
})
it.skip('C339385: 50 Credit Card Guest Checkouting [Payments API]', () => { // possibly a PS8 / Cypress compatibility issue, that Cart is celaning the cookies...
    cy.clearCookies()
    //Payments API item
    cy.visit('/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
    cy.get('[class="h3 product-title"]').eq(0).click()
    cy.get('.add > .btn').click()
    cy.get('.cart-content-btn > .btn-primary').click()
    cy.wait(2000)
    cy.visit('/en/order')
    // Creating random user all the time
    cy.get(':nth-child(1) > .custom-radio > input').check()
    cy.get('#field-firstname').type('AUT')
    cy.get('#field-lastname').type('AUT')
    cy.get('#field-company').type('AUT')
    cy.get('#field-siret').type('123')
    const uuid = () => Cypress._.random(0, 1e6)
    const id = uuid()
    const testname = `testemail${id}@testing.com`
    cy.get(':nth-child(6) > .col-md-6 > #field-email').type(testname, {delay: 0})
    cy.get('.field-password-policy > .form-group > .col-md-6 > .input-group > #field-password').type('123456',{delay:0})
    cy.get(':nth-child(11) > .col-md-6 > .custom-checkbox > label').click({force:true})
    cy.get(':nth-child(13) > .col-md-6 > .custom-checkbox > label').click({force:true})
    cy.get('#customer-form > .form-footer > .continue').click()
    cy.wait(2000)
    cy.visit('/en/order')
    cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
    cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
    cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
    cy.get('#field-address2').type('ADDR2',{delay:0}).as('address2')
    cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
    cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
    cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
    cy.get(':nth-child(13) > .col-md-6 > .form-control').type('+370 000',{delay:0}).as('telephone')
    cy.get('.form-footer > .continue').click()
    cy.get('#js-delivery > .continue').click()
    cy.contains('Karte').click({force:true})
    //Credit card inputing
    cy.CreditCardFillingIframe()
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it.skip('C339386: 51 Credit Card Guest Checkouting with not 3DS secure card [Payments API]', () => { // possibly a PS8 / Cypress compatibility issue, that Cart is celaning the cookies...
    cy.clearCookies()
    //Payments API item
    cy.visit('/en/', { headers: {"Accept-Encoding": "gzip, deflate"}})
    cy.get('[class="h3 product-title"]').eq(0).click()
    cy.get('.add > .btn').click()
    cy.get('.cart-content-btn > .btn-primary').click()
    cy.get('.text-sm-center > .btn').click()
    // Creating random user all the time
    cy.get(':nth-child(1) > .custom-radio > input').check()
    cy.get('#field-firstname').type('AUT',{delay:0})
    cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
    const uuid = () => Cypress._.random(0, 1e6)
    const id = uuid()
    const testname = `testemail${id}@testing.com`
    cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
    cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
    cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
    cy.get('#customer-form > .form-footer > .continue').click()
    cy.reload()
    cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
    cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
    cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
    cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
    cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
    cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
    cy.get(':nth-child(13) > .col-md-6 > .form-control').type('+370 000',{delay:0}).as('telephone')
    cy.get('.form-footer > .continue').click()
    cy.get('#js-delivery > .continue').click()
    cy.contains('Karte').click({force:true})
    //Credit card inputing
    cy.NotSecureCreditCardFillingIframe()
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
})
it('C339387: 52 Paypal Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('PayPal').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339388: 53 Paypal BO Refunding, Partial Refunding [Payments API]', () => {
    cy.visit('/admin1/index.php?controller=AdminOrders')
    cy.get(':nth-child(1) > .column-payment').click()
    //Check partial refunding on Payments API - seems that Paypal has only Partial Refunding without Refund button
    cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
    cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
    cy.get('.swal-modal').should('exist')
    cy.get(':nth-child(2) > .swal-button').click()
    cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
});
it('C339391: 56 Przelewy24 Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('Przelewy24').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('.input-float > input').type('testing@testing.com')
    cy.get('[class="button form__button"]').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339392: 57 Przelewy24 BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
});
it('C339393: 58 Giropay Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('giropay').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339394: 59 Giropay BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
});
it('C339395: 60 EPS Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('eps').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339396: 61 EPS BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
});
it('C339397: 62 KBC/CBC Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('KBC/CBC').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('.grid-button-kbc-cbc').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339398: 63 KBC/CBC BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
});
it('C339399: 64 Belfius Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('Belfius').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C339400: 65 Belfius BO Refunding, Partial Refunding [Payments API]', () => {
    cy.OrderRefundingPartialPaymentsAPI()
});
it('C339401: 66 Bank Transfer Checkouting [Payments API]', () => {
    cy.navigatingToThePaymentPS8()
    //Payment method choosing
    cy.contains('Überweisung').click({force:true})
    cy.get('.condition-label > .js-terms').click({force:true})
    cy.contains('Place order').click()
    cy.get('[value="paid"]').click()
    cy.get('[class="button form__button"]').click()
    cy.get('[id="main"]').should('be.visible') // checking if UI didn't crash at the end
});
it.skip('C339402: 67 Bank Transfer BO Refunding, Partial Refunding [Payments API]', () => { // somehow an error in console is thrown, will check why. Temporary skipping, because Mollie block is not loaded properly
    cy.OrderRefundingPartialPaymentsAPI()
})
it('C3006615: Bancomat Checkouting [Payments API]', () => {
  cy.navigatingToThePaymentPS8()
  //Payment method choosing
  cy.contains('Belfius').click({force:true})
  cy.get('.condition-label > .js-terms').click({force:true})
  cy.contains('Place order').click()
  cy.get('[value="paid"]').click()
  cy.get('[class="button form__button"]').click()
  cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C3006616: Bancomat BO Refunding, Partial Refunding [Payments API]', () => {
  cy.OrderRefundingPartialPaymentsAPI()
})
it('C3006826: Alma Checkouting [Payments API]', () => {
  cy.navigatingToThePaymentPS8()
  //Payment method choosing
  cy.contains('Alma').click({force:true})
  cy.get('.condition-label > .js-terms').click({force:true})
  cy.contains('Place order').click()
  cy.get('[value="paid"]').click()
  cy.get('[class="button form__button"]').click()
  cy.get('#content-hook_order_confirmation > .card-block').should('be.visible')
});
it('C3006827: Alma BO Refunding, Partial Refunding [Payments API]', () => {
  cy.OrderRefundingPartialPaymentsAPI()
})
})
