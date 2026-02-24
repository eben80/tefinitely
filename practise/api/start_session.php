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
    $scenario_instructions = "Crée une mise en situation de type TEF Canada Section A sous la forme d’une annonce ou affiche. L’annonce doit commencer par une consigne claire adressée au candidat, suivie du contenu de l’annonce concernant $chosen. Fournis ensuite la première réplique naturelle du représentant.";
    $system_prompt = "Vous êtes examinateur TEF Canada.

OBJECTIF :
Générer une Section A réaliste.

FORMAT OBLIGATOIRE DU SCÉNARIO :

Le champ \"scenario\" doit être structuré EXACTEMENT comme suit :

1) Une ligne d’instruction adressée au candidat commençant par :
« CONSIGNE : »
Exemple :
CONSIGNE : Vous avez lu l’annonce suivante. Vous téléphonez pour obtenir des renseignements. Posez des questions.

2) Une séparation visuelle (ex: ---)

3) Une annonce rédigée comme une vraie affiche publicitaire.
Elle doit inclure :
- Un titre accrocheur
- Une description claire du service / événement
- Quelques informations partielles (dates, prix, services, conditions, etc.)
- Des informations manquantes afin d’encourager les questions

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
  \"scenario\": \"Texte complet avec CONSIGNE + annonce format affiche\",
  \"assistant_opening\": \"Première phrase naturelle du représentant en français\"
}";
} else {
    $chosen = $categories_en[array_rand($categories_en)];
   $scenario_instructions = "Create a TEF Canada Section A style scenario formatted as an advertisement or poster. The scenario must begin with a clear instruction addressed to the candidate, followed by the advertisement content about $chosen. Then provide the assistant’s first natural spoken line.";
    $system_prompt = "You are an examiner playing the role of the representative connected to the advertisement.

TEF CANADA – SECTION A FORMAT:
- The candidate has read an advertisement/poster.
- They must ask questions to obtain information.
- You respond as the service representative.

LANGUAGE:
- All output must be in English.

ROLES:
- The learner is always the person asking for information.
- You are the representative (seller, organizer, employee, etc.).
- Never speak as the learner.
- Always begin naturally as if responding to an inquiry about the advertisement.

REQUIREMENTS:
- Keep language appropriate for level {$level}.
- Keep interaction realistic and natural.
- Spoken dialogue ONLY in DIALOGUE.
- Corrections ONLY in SUGGESTION.
- Do not provide too much information at once.
- Allow the learner to lead by asking questions.

OUTPUT FORMAT (JSON ONLY):
{
  \"scenario\": \"Clear description of the advertisement and context in English\",
  \"assistant_opening\": \"Representative’s first natural spoken line in English\"
}";$system_prompt = "You are a TEF Canada examiner.

OBJECTIVE:
Generate a realistic Section A scenario.

REQUIRED SCENARIO FORMAT:

The \"scenario\" field must be structured EXACTLY as follows:

1) A candidate instruction line starting with:
“INSTRUCTION:”
Example:
INSTRUCTION: You have read the following advertisement. You call to obtain more information. Ask questions.

2) A visual separator (example: ---)

3) A realistic advertisement/poster including:
- A catchy title
- A clear description of the service/event
- Some partial details (dates, prices, services, conditions, etc.)
- Missing information to encourage questions

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
  \"scenario\": \"Full text including INSTRUCTION + advertisement formatted like a poster\",
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

if (!$data || !isset($data['scenario'], $data['assistant_opening'])) {
    echo json_encode([
        "error" => "Failed to parse OpenAI JSON",
        "raw" => $raw
    ]);
    exit;
}

// ------------------ Initialize session conversation ------------------
$_SESSION['scenario'] = $data['scenario'];
$_SESSION['conversation'] = [
    [
        "role" => "assistant",
        "content" => $data['assistant_opening']
    ]
];

// ------------------ Return to frontend ------------------
echo json_encode([
    "scenario" => $data['scenario'],
    "assistant" => $data['assistant_opening']
]);
