<?php

/**
 * @author Cédric Tanghe <ced.tanghe@gmail.com>
 */

class PHPChecker_Factory
{
    /**
     * @param callable|boolean $pAssertion
     * @param string $pAssertMessage
     * @param string $pSuccessMessage
     * @param string $pFailMessage
     * @param string $pHelpMessage
     */
    public static function createRequirement($pAssertion, 
                                             $pAssertMessage, 
                                             $pSuccessMessage = null, 
                                             $pFailMessage = null, 
                                             $pHelpMessage = null)
    {
        return new PHPChecker_Requirement(
            $pAssertion,
            $pAssertMessage,
            $pSuccessMessage,
            $pFailMessage,
            $pHelpMessage,
            false
        );
    }
    
    /**
     * @param callable|boolean $pAssertion
     * @param string $pAssertMessage
     * @param string $pSuccessMessage
     * @param string $pFailMessage
     * @param string $pHelpMessage
     * @param boolean $pOptional
     */
    public static function createRecommendation($pAssertion, 
                                                $pAssertMessage, 
                                                $pSuccessMessage = null, 
                                                $pFailMessage = null, 
                                                $pHelpMessage = null)
    {
        return new PHPChecker_Requirement(
            $pAssertion,
            $pAssertMessage,
            $pSuccessMessage,
            $pFailMessage,
            $pHelpMessage,
            true
        );
    }
}