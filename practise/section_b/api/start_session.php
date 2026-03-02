<?php
session_start();
require_once __DIR__ . '/openai.php';

header('Content-Type: application/json');

$level = $_POST['level'] ?? 'A2';
$language = $_POST['language'] ?? 'fr'; // 'fr' or 'en'

// ------------------ Broader scenario categories (Idea/Activity/Product) ------------------

$categories = [
    "un festival de musique en plein air engagé pour l'environnement",
    "un cours de cuisine zéro déchet",
    "un voyage humanitaire ou solidaire à l'étranger",
    "une nouvelle application de méditation et bien-être",
    "un service de livraison de repas exclusivement végétaliens",
    "une activité de nettoyage des plages ou forêts",
    "une inscription dans un club de sport très intensif (CrossFit, etc.)",
    "l'achat d'un véhicule électrique partagé entre voisins",
    "la participation à un marathon caritatif pour une cause médicale",
    "un club de lecture sur la philosophie contemporaine",
    "un espace de travail partagé sans Wi-Fi pour booster la concentration",
    "un restaurant immersif sans lumière pour redécouvrir les saveurs",
    "un défi de 30 jours sans réseaux sociaux",
    "une dégustation culinaire à base de grillons et insectes",
    "un système de compostage collectif dans l'immeuble",
    "la journée de la ville sans voiture",
    "un service de troc de vêtements et d'objets",
    "un stage de survie en forêt pour le week-end",
    "un abonnement à une coopérative de produits locaux",
    "une exposition d'art provocatrice sur le climat",
    "remplacer les voitures personnelles par des vélos électriques dans le quartier",
    "adopter la semaine de quatre jours pour gagner en productivité",
    "déménager dans un logement plus petit et écologique (Tiny House)",
    "utiliser un miroir intelligent qui donne des conseils de santé et de mode",
    "confier ses économies à un conseiller financier 100% IA"
];

$categories_en = [
    "an eco-friendly outdoor music festival",
    "a zero-waste cooking class",
    "a humanitarian or solidarity trip abroad",
    "a new meditation and wellness app",
    "an exclusively vegan meal delivery service",
    "a beach or forest cleanup activity",
    "joining a high-intensity sports club (CrossFit, etc.)",
    "buying a shared electric vehicle with neighbors",
    "participating in a charity marathon for a medical cause",
    "a book club on contemporary philosophy",
    "a shared workspace without Wi-Fi to boost concentration",
    "an immersive dark restaurant to rediscover flavors",
    "a 30-day no-social-media challenge",
    "a culinary tasting based on crickets and insects",
    "a collective composting system in the building",
    "Car-Free City Day",
    "a clothing and object swap service",
    "a survival training weekend in the forest",
    "a subscription to a local produce cooperative",
    "a provocative art exhibition about climate change",
    "replacing personal cars with e-bikes in the neighborhood",
    "adopting a four-day work week to increase productivity",
    "moving to a smaller, eco-friendly home (Tiny House)",
    "using a smart mirror that provides health and fashion advice",
    "entrusting savings to a 100% AI financial advisor"
];

// Pick a random category
if ($language === 'fr') {
    $chosen = $categories[array_rand($categories)];
    $scenario_instructions = "Crée une mise en situation de type TEF Canada Section B. Le sujet est : $chosen. L'annonce doit inclure une consigne précise et le contenu de l'annonce. Fournis ensuite la première réplique de l'ami.";
    $system_prompt = "Vous êtes examinateur TEF Canada spécialisé dans la Section B.

OBJECTIF :
Générer une Section B réaliste. Le candidat doit convaincre un ami (vous) d'adopter une idée, de participer à une activité ou d'utiliser un service. Vous devez activement contester ses arguments.

STRUCTURE DE LA RÉPONSE :
1) CONSIGNE : Expliquez que le candidat doit présenter le document, structurer ses arguments avec des exemples, répondre à vos objections et essayer de vous persuader pendant 10 minutes.
2) ANNONCE : Un texte court présentant l'idée/l'activité (Titre, concept, avantages, contraintes).

RÔLES :
- Le candidat est votre ami.
- Vous êtes l'ami initialement opposé ou très sceptique.
- Ne parlez jamais à la place du candidat.

LANGUE :
- Tout en français.
- Niveau : {$level}.

FORMAT DE SORTIE (JSON UNIQUEMENT) :
{
  \"instruction\": \"CONSIGNE : Vous avez lu ce document. Présentez-le à votre ami, développez des arguments structurés avec des exemples et essayez de le convaincre malgré ses réticences. Vous avez 10 minutes.\",
  \"advertisement\": \"Texte de l'annonce\",
  \"assistant_opening\": \"Réplique initiale (ex: Allô ? Oui, je t'écoute, qu'est-ce que tu voulais me proposer ?)\"
}";
} else {
    $chosen = $categories_en[array_rand($categories_en)];
    $scenario_instructions = "Create a TEF Canada Section B style scenario. Subject: $chosen. Include clear instructions and ad content. Provide the friend's first line.";
    $system_prompt = "You are a TEF Canada examiner for Section B.

OBJECTIVE:
Generate a realistic Section B scenario. The candidate must convince a friend (you) to adopt an idea, participate in an activity, or use a service. You must actively challenge their arguments.

ADVERTISEMENT STRUCTURE:
1) INSTRUCTION: State that the candidate must present the document, structure arguments with examples, respond to your objections, and try to persuade you for 10 minutes.
2) ADVERTISEMENT: A short text presenting the idea/activity (Title, concept, benefits, constraints).

ROLES:
- The learner is your friend.
- You are the friend who is initially opposed or very skeptical.
- Never speak as the learner.

LANGUAGE:
- All in English.
- Level: {$level}.

OUTPUT FORMAT (JSON ONLY):
{
  \"instruction\": \"INSTRUCTION: You have read this document. Present it to your friend, provide structured arguments with examples, and try to convince them despite their objections. You have 10 minutes.\",
  \"advertisement\": \"Advertisement text\",
  \"assistant_opening\": \"Initial line (e.g., Hey! Yeah, I'm listening, what did you want to tell me about?)\"
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
