<?php
ini_set('display_errors','On');
ini_set('error_reporting', E_ALL & ~E_NOTICE);

include('facts.php');
include('jokes.php');

$input = json_decode(file_get_contents("php://input"), true, 512);

$applicationId = $input['session']['application']['applicationId'];

if ($applicationId !== 'amzn1.ask.skill.9651cfcf-a76c-45ae-b9c5-0a3aa129dfca') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

$requestType = $input['request']['type'];
$locale = $input['request']['locale'];

function getReply($replyKey, $locale) {
    // Cut down the locale because we don't care about the different country codes: en-US/en-UK/de-DE/...
    $locale = substr($locale, 0, 2);

    $replies = array(
        'onOpen' => array(
            'en' => '<p>Hello, I am alexa tool-time. What would you like me to do?</p>',
            'de' => ''
        ),
        'StopIntent' => array(
            'en' => '',
            'de' => ''
        ),
        'noIntent' => array(
            'en' => 'Hmm, I didn\'t quite get that.',
            'de' => 'Puh. Keine Ahnung was das bedeuten soll.'
        ),
        'onSessionEnd' => array(
            'en' => '',
            'de' => ''
        ),
        'reprompt' => array(
            'en' => 'Have you figured out what you want to ask?',
            'de' => 'Möchtest Du sonst noch etwas wissen?'
        )
    );

    return '<speak>' . $replies[$replyKey][$locale] . '</speak>';
}

switch ($requestType) {
    case 'LaunchRequest':
        onLaunchRequest($locale);
        break;
    case 'IntentRequest':
        onIntentRequest($input['request']['intent']['name'], $input['request']['intent']['slots'], key_exists('attributes', $input['session']) ? $input['session']['attributes'] : null, $locale);
        break;
    case 'SessionEndedRequest':
        onSessionEndedRequest($locale);
        break;
}

function onLaunchRequest($locale) {
    sendResponse($locale, getReply('onOpen', $locale));
}

function onIntentRequest($intentName, $slots, $attributes, $locale) {
    global $catFacts, $dogFacts, $spaceFacts;
    global $dinosaurJokes, $germanJokes, $randomJokes;

    switch ($intentName) {
        case 'jokeIntent':
            $jokeArray = $randomJokes;

            if (isset($slots['jokeType'])) {
                $slotValue = strtolower($slots['jokeType']['value']);
                if ($slotValue == 'german' || $slotValue == 'germans') {
                    $jokeArray = $germanJokes;
                } else if ($slotValue == 'dinosaur' || $slotValue == 'dinosaurs') {
                    $jokeArray = $dinosaurJokes;
                }
            }

            $joke = '<speak>' . $jokeArray[rand(0, count($jokeArray) - 1)] . '</speak>';
            sendResponse($locale, $joke, null, null, true);
            break;

        case 'factIntent':
            $factArray = $spaceFacts;

            if (isset($slots['factType'])) {
                $slotValue = strtolower($slots['factType']['value']);
                if ($slotValue == 'dog' || $slotValue == 'dogs') {
                    $factArray = $dogFacts;
                } else if ($slotValue == 'cat' || $slotValue == 'cats') {
                    $factArray = $catFacts;
                }
            }

            $fact = '<speak>' . $factArray[rand(0, count($factArray) - 1)] . '</speak>';
            sendResponse($locale, $fact, null, null, true);
            break;

        case 'weatherIntent':
            $weatherFeelings = array('supreme', 'wonderfull', 'gorgeous', 'extraordinary', 'exceptional', 'exquisite');
            $weatherTypes = array('so sunny you will need two sunglasses', 'pouring raing like the monsoon', 'so dry you will need a cocktail every hour', 'raining hail so big you could play baseball');

            $weatherResponse = '<p>The weather will be extremely ' . $weatherFeelings[rand(0, count($weatherFeelings) - 1)];

            if (isset($slots['city']) && isset($slots['city']['value'])) {
                $weatherResponse .= ' in ' . $slots['city']['value'];
            }

            $weatherResponse .= '</p>';
            $weatherResponse = $weatherResponse . 'It will be ' . $weatherTypes[rand(0, count($weatherTypes) - 1)];
            sendResponse($locale, '<speak>' . $weatherResponse . '</speak>', null, null, true);
            break;

        case 'AMAZON.YesIntent':
            break;

        case 'AMAZON.StopIntent':
        case 'AMAZON.CancelIntent':
            sendResponse($locale, getReply('StopIntent', $locale), null, null, true);
            break;

        default:
            sendResponse($locale, getReply('noIntent', $locale));
    }
}

function onSessionEndedRequest($locale) {
    sendResponse($locale, getReply('onSessionEnd', $locale));
}

function sendResponse($locale, $ssml, $sessionAttributes = null, $card = null, $shouldEndSession = false) {
    if ($sessionAttributes === null) {
        $sessionAttributes = array();
    }

    $responseArray = array(
        'version' => '1.0',
        'sessionAttributes' => $sessionAttributes,
        'response' => array(
            'outputSpeech' => array(
                'type' => 'SSML',
                'ssml' => $ssml
            ),
            'reprompt' => array(
                'outputSpeech' => array(
                    'type' => 'SSML',
                    'ssml' => getReply('reprompt', $locale)
            )),
            'shouldEndSession' => $shouldEndSession
        )
    );

    if ($card !== null) {
        $responseArray['response']['card'] = $card;
    }

    $responseJSON = json_encode($responseArray);

    header('Content-Type: application/json;charset=UTF-8');
    header('Content-Length: ' . strlen($responseJSON));
    echo $responseJSON;
}
