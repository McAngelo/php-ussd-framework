<?php

/*
 *  (c) 2016. SMSGH
 */

namespace UssdFramework;

/**
 *
 * Enables framework users to be notified of pre- and post- ussd request
 * processing events through {@link Ussd#requestListener(
 * com.smsgh.ussd.framework.UssdRequestListener) }.
 * 
 * @author Aaron Baffour-Awuah
 */
interface UssdRequestListener {
    
    /**
     * Called just before ussd request is processed.
     * @param UssdRequest $ussdRequest ussd request to be processed.
     */
    function requestEntering($ussdRequest);
    
    /**
     * Called just after ussd response is obtained.
     * @param UssdRequest $ussdRequest the ussd request which was processed.
     * @param UssdResponse $ussdResponse the ussd response obtained.
     */
    function responseLeaving($ussdRequest, $ussdResponse);
}
