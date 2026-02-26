<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$level = $_POST['level'] ?? 'A2';
$language = $_POST['language'] ?? 'fr'; // 'fr' or 'en'

// ------------------ Broader scenario categories ------------------

$categories = [
    "un magasin de prêt-sur-gage qui rachète des bijoux",
    "un service de location de bateaux",
    "une boutique en ligne qui vend des lunettes",
    "un service de bénévolat",
    "une banque 100% en ligne sans agence physique et sans frais de tenue de compte",
    "une bibliothèque numérique avec inscription en ligne et livres en version numérique",
    "une annonce pour un spectacle musical avec pass 1 jour ou 2 jours et possibilité de passer la nuit",
    "une agence qui organise des fêtes et événements",
    "un sondage auprès du public",
    "une activité de scooter nautique",
    "un service d’entretien automobile",
    "un magasin de costumes et déguisements",
    "un service de garde pour animaux",
    "une boulangerie qui fait des livraisons de petit-déjeuner",
    "une exposition d’art culinaire intitulée « Un printemps de chefs »",
    "des vacances à bord d’un train touristique",
    "un festival de musique organisé par un club dans un parc",
    "une activité de football en plein air avec bulles géantes",
    "un club de marche",
    "le nouveau catalogue d’une agence de voyages",
    "un séjour dans un château entre montagne et rivière avec ateliers gastronomiques",
    "une bibliothèque locale qui propose réservation de salles, prêt de livres et activités pour enfants",
    "une école de ski et de snowboard",
    "une discothèque installée dans un bus",
    "une formation en parachutisme",
    "un théâtre qui organise un spectacle spécial pour enfants"
];

$categories_en = [
    "a pawn shop that buys jewelry",
    "a boat rental service",
    "an online store that sells eyeglasses",
    "a volunteer service",
    "a 100% online bank with no physical branches and no account fees",
    "a digital library with online registration and e-books",
    "a musical show with 1-day or 2-day passes and overnight stay option",
    "an event planning agency for parties",
    "a public survey",
    "a jet ski activity",
    "a car maintenance service",
    "a costume clothing store",
    "a pet care service",
    "a bakery that delivers breakfast",
    "a culinary art exhibition called 'Un printemps de chefs'",
    "a vacation spent on a tourist train",
    "a music festival organized by a club in a park",
    "an outdoor football activity played with giant bubble suits",
    "a walking club",
    "a travel agency’s new catalogue",
    "a stay in a château between mountains and a river with food workshops",
    "a local library offering room reservations, book borrowing and children’s activities",
    "a ski and snowboarding school",
    "a nightclub inside a bus",
    "a parachute training course",
    "a theatre organizing a special show for children"
];

// Pick a random category
if ($language === 'fr') {
    $chosen = $categories[array_rand($categories)];
    $scenario_instructions = "Crée une mise en situation de type TEF Canada Section A sous la forme d’une annonce ou affiche. L’annonce doit inclure une consigne claire adressée au candidat, suivie du contenu de l’annonce concernant $chosen. Fournis ensuite la première réplique naturelle du représentant.";
    $system_prompt = "Vous êtes examinateur TEF Canada.

OBJECTIF :
Générer une Section A réaliste.

STRUCTURE DE L'ANNONCE :
1) Une ligne d’instruction adressée au candidat commençant par : « CONSIGNE : ». Cette consigne DOIT préciser que le candidat doit poser environ 10 à 15 questions naturellement en 5 minutes.
2) Une annonce rédigée comme une vraie affiche publicitaire (Titre, description, infos partielles).

IMPORTANT :
- Ne donnez PAS toutes les informations.
- L’annonce doit encourager au moins 10 questions potentielles.
- Style authentique d’affiche ou publicité.

RÔLES :
- L’apprenant est toujours la personne qui appelle ou se renseigne.
- Vous êtes le représentant lié à l’annonce.
- Ne parlez jamais à la place du candidat.
- Commencez naturellement la conversation comme si le candidat appelait.

LANGUE :
- Tout en français.
- Niveau adapté : {$level}.

FORMAT DE SORTIE (JSON UNIQUEMENT) :
{
  \"instruction\": \"La ligne de consigne uniquement (ex: CONSIGNE : Vous avez lu l'annonce suivante. Vous téléphonez pour obtenir des renseignements. Vous devez poser environ 10 à 15 questions naturellement en 5 minutes.)\",
  \"advertisement\": \"Le texte de l'affiche publicitaire uniquement (avec titre et détails)\",
  \"assistant_opening\": \"Première phrase naturelle du représentant en français\"
}";
} else {
    $chosen = $categories_en[array_rand($categories_en)];
    $scenario_instructions = "Create a TEF Canada Section A style scenario formatted as an advertisement or poster. The scenario must include a clear instruction addressed to the candidate, followed by the advertisement content about $chosen. Then provide the assistant’s first natural spoken line.";
    $system_prompt = "You are a TEF Canada examiner.

OBJECTIVE:
Generate a realistic Section A scenario.

ADVERTISEMENT STRUCTURE:
1) A candidate instruction line starting with: “INSTRUCTION:”. This instruction MUST state that the candidate is expected to ask around 10–15 questions naturally within 5 minutes.
2) A realistic advertisement/poster (Title, description, partial details).

IMPORTANT:
- Do NOT provide complete information.
- The advertisement must allow at least 10 possible follow-up questions.
- It must look like a real promotional ad or poster.

ROLES:
- The learner is always the person calling or enquiring.
- You are the representative connected to the advertisement.
- Never speak as the learner.
- Start naturally as if answering their call.

LANGUAGE:
- All in English.
- Keep language appropriate for level {$level}.

OUTPUT FORMAT (JSON ONLY):
{
  \"instruction\": \"The instruction line only (e.g., INSTRUCTION: You have read the following advertisement. You call to obtain more information. You are expected to ask around 10–15 questions naturally within 5 minutes.)\",
  \"advertisement\": \"The advertisement poster text only (with title and details)\",
  \"assistant_opening\": \"Representative’s first natural spoken line in English\"
}";
}

// ------------------ SYSTEM prompt ------------------
$messages = [
    [
        "role" => "system",
        "content" => $system_prompt
    ],
    [
        "role" => "user",
        "content" => $scenario_instructions
    ]
];

// ------------------ Call OpenAI ------------------
$response = openai_chat($messages);
$raw = $response['content'] ?? '';

// ------------------ Robust JSON extraction ------------------
preg_match('/\{(?:[^{}]|(?R))*\}/', $raw, $matches);

if (empty($matches)) {
    echo json_encode([
        "error" => "Could not find JSON in OpenAI response",
        "raw" => $raw
    ]);
    exit;
}

$jsonText = $matches[0];
$data = json_decode($jsonText, true);

if (!$data || !isset($data['instruction'], $data['advertisement'], $data['assistant_opening'])) {
    echo json_encode([
        "error" => "Failed to parse OpenAI JSON",
        "raw" => $raw
    ]);
    exit;
}

// ------------------ Initialize session conversation ------------------
$combined_scenario = $data['instruction'] . "\n\n" . $data['advertisement'];
$_SESSION['scenario'] = $combined_scenario;
$_SESSION['advertisement'] = $data['advertisement'];
$_SESSION['language'] = $language;
unset($_SESSION['hints']);
$_SESSION['conversation'] = [
    [
        "role" => "assistant",
        "content" => $data['assistant_opening']
    ]
];

// ------------------ Return to frontend ------------------
echo json_encode([
    "instruction" => $data['instruction'],
    "advertisement" => $data['advertisement'],
    "assistant" => $data['assistant_opening']
]);
