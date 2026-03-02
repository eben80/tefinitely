<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$level = $_POST['level'] ?? 'A2';
$language = $_POST['language'] ?? 'fr'; // 'fr' or 'en'

// ------------------ Broader scenario categories ------------------

$categories = [
    "un festival de musique en plein air",
    "un cours de cuisine thématique",
    "un voyage organisé dans une destination insolite",
    "une nouvelle application de fitness",
    "un service de livraison de repas sains",
    "une activité de bénévolat pour l'environnement",
    "une inscription dans une salle de sport innovante",
    "l'achat d'une voiture électrique",
    "la participation à un marathon ou une course solidaire",
    "un club de lecture ou de discussion",
    "un espace de coworking moderne",
    "un restaurant avec un concept original (ex: manger dans le noir)",
    "un saut en parachute ou une activité extrême",
    "une expérience culinaire à base d'insectes",
    "l'utilisation d'huile de ricin pour l'entretien de la voiture",
    "la journée de la ville propre",
    "un service de conciergerie à domicile",
    "une séance de yoga en plein air",
    "un abonnement à une bibliothèque numérique",
    "une exposition d'art contemporain"
];

$categories_en = [
    "an outdoor music festival",
    "a themed cooking class",
    "an organized trip to an unusual destination",
    "a new fitness app",
    "a healthy meal delivery service",
    "an environmental volunteering activity",
    "a membership at an innovative gym",
    "buying an electric car",
    "participating in a marathon or charity run",
    "a book or discussion club",
    "a modern coworking space",
    "a restaurant with an original concept (e.g., dining in the dark)",
    "skydiving or an extreme activity",
    "a culinary experience based on insects",
    "using castor oil for car maintenance",
    "Clean City Day",
    "a home concierge service",
    "an outdoor yoga session",
    "a digital library subscription",
    "a contemporary art exhibition"
];

// Pick a random category
if ($language === 'fr') {
    $chosen = $categories[array_rand($categories)];
    $scenario_instructions = "Crée une mise en situation de type TEF Canada Section B. L'annonce doit porter sur : $chosen. L'annonce doit inclure une consigne claire adressée au candidat, suivie du contenu de l'annonce. Fournis ensuite la première réplique naturelle de l'ami (l'examinateur).";
    $system_prompt = "Vous êtes examinateur TEF Canada.

OBJECTIF :
Générer une Section B réaliste. Dans cette section, le candidat doit convaincre un ami (vous) de participer à une activité, d'utiliser un service ou d'acheter un produit basé sur une annonce.

STRUCTURE DE LA RÉPONSE :
1) Une ligne d’instruction adressée au candidat commençant par : « CONSIGNE : ». Cette consigne DOIT préciser que le candidat doit présenter le document à son ami et essayer de le convaincre d'y participer ou de l'utiliser pendant environ 10 minutes.
2) Une annonce courte et attractive (Titre, points forts, prix ou date).

RÔLES :
- Le candidat (l'utilisateur) est votre ami.
- Vous êtes l'ami qui reçoit l'appel ou la proposition.
- Soyez naturel, un peu sceptique ou indifférent au début pour laisser de la place à l'argumentation.
- Ne parlez jamais à la place du candidat.

LANGUE :
- Tout en français.
- Niveau adapté : {$level}.

FORMAT DE SORTIE (JSON UNIQUEMENT) :
{
  \"instruction\": \"La ligne de consigne uniquement (ex: CONSIGNE : Vous avez lu cette annonce. Présentez-la à votre ami et essayez de le convaincre d'y participer avec vous. Vous avez 10 minutes.)\",
  \"advertisement\": \"Le texte de l'annonce uniquement\",
  \"assistant_opening\": \"Première réplique naturelle de l'ami (ex: Allô ? Oui, salut ! Quoi de neuf ?)\"
}";
} else {
    $chosen = $categories_en[array_rand($categories_en)];
    $scenario_instructions = "Create a TEF Canada Section B style scenario. The ad should be about: $chosen. The scenario must include a clear instruction addressed to the candidate, followed by the advertisement content. Then provide the friend's (examiner's) first natural spoken line.";
    $system_prompt = "You are a TEF Canada examiner.

OBJECTIVE:
Generate a realistic Section B scenario. In this section, the candidate must convince a friend (you) to participate in an activity, use a service, or buy a product based on an advertisement.

ADVERTISEMENT STRUCTURE:
1) A candidate instruction line starting with: “INSTRUCTION:”. This instruction MUST state that the candidate is expected to present the document to their friend and try to convince them to join or use it for about 10 minutes.
2) A realistic short advertisement (Title, highlights, price or date).

ROLES:
- The learner (user) is your friend.
- You are the friend receiving the call or proposal.
- Be natural, slightly skeptical or indifferent at first to allow for persuasion.
- Never speak as the learner.

LANGUAGE:
- All in English.
- Keep language appropriate for level {$level}.

OUTPUT FORMAT (JSON ONLY):
{
  \"instruction\": \"The instruction line only (e.g., INSTRUCTION: You have read this advertisement. Present it to your friend and try to convince them to join you. You have 10 minutes.)\",
  \"advertisement\": \"The advertisement text only\",
  \"assistant_opening\": \"Friend's first natural spoken line (e.g., Hey! How's it going? What's up?)\"
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
