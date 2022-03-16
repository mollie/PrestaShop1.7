/// <reference types="Cypress" />
context('PS177 Tests Suite', () => {
  beforeEach(() => {
    cy.viewport(1920,1080)
  })
it('01 Connecting test API successsfully', () => {
      cy.clearLocalStorage()
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
    login('MollieBOLoggingIn')
    cy.visit('/admin1/index.php')
    cy.get('#subtab-AdminMollieModule > .link').click()
    //switching the multistore
    cy.get('#header_shop > .dropdown').click()
    cy.get('#header_shop > .dropdown > .dropdown-menu').click(100,100)
    //
    cy.get('[for="MOLLIE_ACCOUNT_SWITCH_on"]').click()
    cy.get('#MOLLIE_API_KEY_TEST').type('test_pACCABA9KvWGjvW9StKn7QTDNgMvzh')
    cy.get('#module_form_submit_btn').click()
})
it('02 Enabling Mollie carriers successfully', () => {
        //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php')
      cy.get('#subtab-AdminParentPayment > :nth-child(1)').click()
      cy.get('#subtab-AdminPaymentPreferences > .link').click()
      //switching the multistore
      cy.get('.arrow-down').click()
      cy.get(':nth-child(3) > .dropdown-item').click()
      //
      cy.get('[class="js-multiple-choice-table-select-column"]').eq(6).click()
      cy.get('[class="btn btn-primary"]').eq(3).click()
})
it('03 Enabling All payments in Module BO [Orders API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      //switching the multistore
      cy.get('#header_shop > .dropdown').click()
      cy.get('#header_shop > .dropdown > .dropdown-menu').click(100,100)
      //
      cy.ConfOrdersAPI()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('04 Bancontact Checkouting [Orders API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Bancontact').click({force:true})
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('05 Bancontact Order BO Shiping, Refunding [Orders API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('06 iDEAL Checkouting [Orders API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('iDEAL').click({force:true})
      //If issuer dropdown is enabled
      // cy.get('#mollie-issuer-dropdown-button').click()
      // cy.get('[data-ideal-issuer="ideal_ABNANL2A"]').click()
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('07 iDEAL Order BO Shiping, Refunding [Orders API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('08 Klarna Slice It Checkouting [Orders API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Slice it.').click({force:true})
      cy.get('[type="checkbox"]').check({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('09 Klarna Slice It Order BO Shiping, Refunding [Orders API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('10 Klarna Pay Later Checkouting [Orders API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.contains('DE').click()
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Pay later.').click({force:true})
      cy.get('[type="checkbox"]').check({force:true})
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="authorized"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('11 Klarna Pay Later Order BO Shiping, Refunding [Orders API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('12 Credit Card Checkouting [Orders API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('13 Credit Card Order BO Shiping, Refunding [Orders API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      //Refunding dropdown in React
      cy.get('.btn-group-action > .btn-group > .dropdown-toggle').eq(0).click()
      cy.get('[role="button"]').eq(2).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('[class="alert alert-success"]').should('be.visible')
      //Shipping button in React
      cy.get('.btn-group > [title=""]').eq(0).click()
      cy.get('[class="swal-button swal-button--confirm"]').click()
      cy.get('.swal-modal').should('exist')
      cy.get('#input-carrier').clear({force: true}).type('FedEx',{delay:0})
      cy.get('#input-code').clear({force: true}).type('123456',{delay:0})
      cy.get('#input-url').clear({force: true}).type('https://www.invertus.eu',{delay:0})
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Shipment was made successfully!')
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('14 Enabling All payments in Module BO [Payments API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/')
      cy.get('#subtab-AdminMollieModule > .link').click()
      //switching the multistore
      cy.get('#header_shop > .dropdown').click()
      cy.get('#header_shop > .dropdown > .dropdown-menu').click(100,100)
      //
      cy.ConfPaymentsAPI()
      cy.get('[type="submit"]').first().click()
      cy.get('[class="alert alert-success"]').should('be.visible')
})
it('15 Bancontact Checkouting [Payments API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Bancontact').click({force:true})
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('16 Bancontact Order BO Shiping, Refunding [Payments API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('17 iDEAL Checkouting [Payments API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('iDEAL').click({force:true})
      //If issuer dropdown is enabled
      // cy.get('#mollie-issuer-dropdown-button').click()
      // cy.get('[data-ideal-issuer="ideal_ABNANL2A"]').click()
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('.payment-method-list > :nth-child(1)').click()
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('18 iDEAL Order BO Shiping, Refunding [Payments API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('19 Credit Card Checkouting  [Payments API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Caching logging-in to FO
      var loginFO = (MollieFOLoggingIn) => {
      cy.session(MollieFOLoggingIn,() => {
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=my-account')
      cy.get('[type="email"]').eq(0).type('demo@demo.com')
      cy.get('[type="password"]').eq(0).type('demodemo')
      cy.get('[type="submit"]').eq(1).click({force:true})
      cy.get('#history-link > .link-item').click()
      cy.contains('Reorder').click()
      })
    }
      loginFO('MollieFOLoggingIn')
      cy.visit('https://demoshop.eu.ngrok.io/index.php?controller=history')
      //
      cy.contains('Reorder').click()
      //Billing country LT, DE etc.
      cy.get('.clearfix > .btn').click()
      cy.get('#js-delivery > .continue').click()
      //Payment method choosing
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
it('20 Credit Card Order BO Shiping, Refunding [Payments API]', () => {
      //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      cy.visit('/admin1/index.php?controller=AdminOrders')
      cy.get(':nth-child(1) > .column-payment').click()
      cy.get('#mollie_order > :nth-child(1)').should('exist')
      cy.get('.form-inline > :nth-child(1) > .btn').should('exist')
      cy.get('.input-group-btn > .btn').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body > :nth-child(3)').should('exist')
      cy.get('.card-body > :nth-child(6)').should('exist')
      cy.get('.card-body > :nth-child(9)').should('exist')
      cy.get('#mollie_order > :nth-child(1) > :nth-child(1)').should('exist')
      cy.get('.sc-htpNat > .panel > .card-body').should('exist')
      cy.get('.sc-bxivhb > .panel > .panel-heading').should('exist')
      cy.get('.sc-bxivhb > .panel > .card-body').should('exist')
      //check partial refunding on Payments API
      cy.get('.form-inline > :nth-child(2) > .input-group > .form-control').type('1.51',{delay:0})
      cy.get(':nth-child(2) > .input-group > .input-group-btn > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
      cy.get('.form-inline > :nth-child(1) > .btn').click()
      cy.get('.swal-modal').should('exist')
      cy.get(':nth-child(2) > .swal-button').click()
      cy.get('#mollie_order > :nth-child(1) > .alert').contains('Refund was made successfully!')
})
it('21 Credit Card Guest Checkouting [Payments API]', () => {
    //Caching the session
      var login = (MollieBOLoggingIn) => {
      cy.session(MollieBOLoggingIn,() => {
      cy.visit('/admin1/')
      cy.url().should('contain', 'https').as('Check if HTTPS exists')
      cy.get('#email').type('demo@demo.com',{delay: 0, log: false})
      cy.get('#passwd').type('demodemo',{delay: 0, log: false})
      cy.get('#submit_login').click().wait(1000).as('Connection successsful')
      cy.visit('/admin1/')
      })
    }
      //
      login('MollieBOLoggingIn')
      //Orders API item
      cy.visit('', { headers: {"Accept-Encoding": "gzip, deflate"}})
      cy.get('[class="h3 product-title"]').eq(0).click()
      cy.get('.add > .btn').click()
      cy.get('.cart-content-btn > .btn-primary').click()
      cy.get('.text-sm-center > .btn').click()

      // Creating random user all the time
      cy.get(':nth-child(1) > .custom-radio > input').check()
      cy.get('#customer-form > section > :nth-child(2) > .col-md-6 > .form-control').type('AUT',{delay:0})
      cy.get(':nth-child(3) > .col-md-6 > .form-control').type('AUT',{delay:0})
      const uuid = () => Cypress._.random(0, 1e6)
      const id = uuid()
      const testname = `testemail${id}@testing.com`
      cy.get(':nth-child(4) > .col-md-6 > .form-control').type(testname, {delay: 0})
      cy.get(':nth-child(6) > .col-md-6 > .input-group > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(9) > .col-md-6 > .custom-checkbox > label > input').check()
      cy.get('#customer-form > .form-footer > .continue').click()
      cy.get(':nth-child(6) > .col-md-6 > .form-control').type('123456',{delay:0})
      cy.get(':nth-child(7) > .col-md-6 > .form-control').type('123456',{delay:0}).as('vat number')
      cy.get(':nth-child(8) > .col-md-6 > .form-control').type('ADDR',{delay:0}).as('address')
      cy.get(':nth-child(10) > .col-md-6 > .form-control').type('54469',{delay:0}).as('zip')
      cy.get(':nth-child(11) > .col-md-6 > .form-control').type('CIT',{delay:0}).as('city')
      cy.get(':nth-child(12) > .col-md-6 > .form-control').select('Lithuania').as('country')
      cy.get(':nth-child(13) > .col-md-6 > .form-control').type('+370 000',{delay:0}).as('telephone')
      cy.get('.form-footer > .continue').click()
      cy.get('#js-delivery > .continue').click()
      cy.contains('Credit card').click({force:true})
      //Credit card inputing
      cy.frameLoaded('[data-testid=mollie-container--cardHolder] > iframe')
      cy.enter('[data-testid=mollie-container--cardHolder] > iframe').then(getBody => {
      getBody().find('#cardHolder').clear({force: true}).type('TEST TEEESSSTT')
      })
      cy.enter('[data-testid=mollie-container--cardNumber] > iframe').then(getBody => {
      getBody().find('#cardNumber').clear({force: true}).type('5555555555554444')
      })
      cy.enter('[data-testid=mollie-container--expiryDate] > iframe').then(getBody => {
      getBody().find('#expiryDate').clear({force: true}).type('1222')
      })
      cy.enter('[data-testid=mollie-container--verificationCode] > iframe').then(getBody => {
      getBody().find('#verificationCode').clear({force: true}).type('222')
      })
      cy.get('[type="checkbox"]').check()
      cy.get('.ps-shown-by-js > .btn').click()
      cy.setCookie(
        'SESSIONID',
        "cypress-dummy-value",
        {
            domain: '.www.mollie.com',
            sameSite: 'None',
            secure: true,
            httpOnly: true
        }
      );    // reload current page to activate cookie
      cy.reload();
      cy.get('[value="paid"]').click()
      cy.get('[class="button form__button"]').click()
      //should be success screen, possible bug
})
})