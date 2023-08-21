<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{    
    const SUPPORTED_LANGUAGES = ['fr', 'en'];

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if($request->get('method') === 'GET' || $request->get('method') === null){
            $acceptedLanguagesArray = explode(";", $request->headers->get('accept-language'));
            $preferredLanguage = explode(",", $acceptedLanguagesArray[0]);
            
            if(strlen($preferredLanguage[0]) > strlen($preferredLanguage[1])){
                $preferredLanguageSmall = $preferredLanguage[1];
            }
            else {
                $preferredLanguageSmall = $preferredLanguage[0];
            }

            if(in_array($preferredLanguageSmall, self::SUPPORTED_LANGUAGES)){
                $request->setLocale($preferredLanguageSmall);
            }
            else {
                $request->setLocale('en');
            }
        }
    }
}