-- Add more dialogues for Phase 1: Shadowing Practice

-- Dialogue 1: Inscription à un cours de poterie
INSERT INTO `dialogues` (`dialogue_name`, `theme`, `section`) VALUES ('Inscription à un cours de poterie', 'les_loisirs', 'section_a');
SET @dialogue_id = LAST_INSERT_ID();
INSERT INTO `dialogue_lines` (`dialogue_id`, `speaker`, `line_text`, `line_order`) VALUES
(@dialogue_id, 'Student', 'Bonjour, je vous appelle au sujet de l\'annonce pour les cours de poterie. J\'aimerais avoir quelques informations.', 1),
(@dialogue_id, 'Instructor', 'Bonjour ! Avec plaisir. Que voulez-vous savoir ?', 2),
(@dialogue_id, 'Student', 'Pour commencer, quels sont les horaires des cours pour débutants ?', 3),
(@dialogue_id, 'Instructor', 'Les cours pour débutants ont lieu le mardi soir de 18h à 20h et le samedi matin de 10h à 12h.', 4),
(@dialogue_id, 'Student', 'D\'accord. Et quel est le tarif pour une session ?', 5),
(@dialogue_id, 'Instructor', 'La session de 8 cours est à 250 $, matériel inclus.', 6),
(@dialogue_id, 'Student', 'Est-ce qu\'il faut apporter quelque chose de spécial ?', 7),
(@dialogue_id, 'Instructor', 'Non, nous fournissons tout. Venez simplement avec des vêtements que vous ne craignez pas de salir !', 8),
(@dialogue_id, 'Student', 'C\'est bon à savoir. Le cours est-il adapté aux vrais débutants ? Je n\'ai jamais fait de poterie.', 9),
(@dialogue_id, 'Instructor', 'Absolument, le cours est conçu pour les débutants. Nous commençons par les bases.', 10),
(@dialogue_id, 'Student', 'Y a-t-il une taille maximale pour les groupes ?', 11),
(@dialogue_id, 'Instructor', 'Oui, nous limitons les groupes à 8 personnes pour garantir une attention personnalisée.', 12),
(@dialogue_id, 'Student', 'Quelle est l\'adresse exacte du studio ?', 13),
(@dialogue_id, 'Instructor', 'Nous sommes au 45 Rue de la Créativité, dans le quartier des artisans.', 14),
(@dialogue_id, 'Student', 'Une dernière question, est-il possible de faire un cours d\'essai avant de s\'engager pour la session complète ?', 15),
(@dialogue_id, 'Instructor', 'Oui, nous proposons un cours d\'essai à 35$. Si vous vous inscrivez ensuite, le prix est déduit du total.', 16);


-- Dialogue 2: Location d'un appartement
INSERT INTO `dialogues` (`dialogue_name`, `theme`, `section`) VALUES ('Location d\'un appartement', 'le_logement', 'section_a');
SET @dialogue_id = LAST_INSERT_ID();
INSERT INTO `dialogue_lines` (`dialogue_id`, `speaker`, `line_text`, `line_order`) VALUES
(@dialogue_id, 'Caller', 'Bonjour, je suis intéressé(e) par l\'appartement à louer que j\'ai vu sur internet. Est-il toujours disponible ?', 1),
(@dialogue_id, 'Agent', 'Bonjour. Oui, l\'appartement au 123 rue de la Paix est toujours disponible.', 2),
(@dialogue_id, 'Caller', 'Pourriez-vous me dire à quel étage il se situe ?', 3),
(@dialogue_id, 'Agent', 'Il est au troisième étage. Il y a un ascenseur dans l\'immeuble.', 4),
(@dialogue_id, 'Caller', 'Très bien. Le loyer inclut-il les charges comme le chauffage et l\'électricité ?', 5),
(@dialogue_id, 'Agent', 'Non, le chauffage et l\'électricité sont à la charge du locataire. L\'eau chaude est incluse.', 6),
(@dialogue_id, 'Caller', 'Est-ce que les animaux de compagnie sont autorisés ? J\'ai un petit chat.', 7),
(@dialogue_id, 'Agent', 'Oui, les petits animaux sont acceptés.', 8),
(@dialogue_id, 'Caller', 'L\'appartement dispose-t-il d\'un balcon ou d\'une terrasse ?', 9),
(@dialogue_id, 'Agent', 'Oui, il y a un petit balcon qui donne sur la cour intérieure.', 10),
(@dialogue_id, 'Caller', 'Y a-t-il une place de parking incluse avec l\'appartement ?', 11),
(@dialogue_id, 'Agent', 'Il n\'y a pas de place de parking attitrée, mais il est possible de louer une place dans le garage souterrain pour 100$ par mois.', 12),
(@dialogue_id, 'Caller', 'Quelle est la durée minimale du bail ?', 13),
(@dialogue_id, 'Agent', 'Le bail est d\'une durée minimale d\'un an.', 14),
(@dialogue_id, 'Caller', 'Quels sont les documents nécessaires pour déposer un dossier ?', 15),
(@dialogue_id, 'Agent', 'Il nous faudra une pièce d\'identité, vos trois derniers bulletins de salaire et une attestation de votre ancien propriétaire si possible.', 16);


-- Dialogue 3: Bénévolat pour un festival de musique
INSERT INTO `dialogues` (`dialogue_name`, `theme`, `section`) VALUES ('Bénévolat pour un festival de musique', 'la_culture', 'section_a');
SET @dialogue_id = LAST_INSERT_ID();
INSERT INTO `dialogue_lines` (`dialogue_id`, `speaker`, `line_text`, `line_order`) VALUES
(@dialogue_id, 'Volunteer', 'Bonjour, j\'ai vu votre appel à bénévoles pour le festival de musique et je suis très intéressé.', 1),
(@dialogue_id, 'Coordinator', 'Bonjour ! Merci de votre intérêt. Avez-vous des questions ?', 2),
(@dialogue_id, 'Volunteer', 'Oui. Quelles sont les dates exactes du festival ?', 3),
(@dialogue_id, 'Coordinator', 'Le festival se déroulera du 10 au 12 août.', 4),
(@dialogue_id, 'Volunteer', 'Et combien d\'heures de bénévolat sont demandées par jour ?', 5),
(@dialogue_id, 'Coordinator', 'Nous demandons un engagement de 6 heures par jour. En échange, vous avez un accès gratuit à tous les concerts.', 6),
(@dialogue_id, 'Volunteer', 'C\'est super ! Faut-il avoir une expérience particulière ?', 7),
(@dialogue_id, 'Coordinator', 'Aucune expérience n\'est requise pour les postes d\'accueil ou d\'information, mais une expérience en secourisme est un plus.', 8),
(@dialogue_id, 'Volunteer', 'Est-ce que la nourriture et les boissons sont fournies pour les bénévoles ?', 9),
(@dialogue_id, 'Coordinator', 'Oui, un repas par jour et des boissons sont offerts à tous nos bénévoles.', 10),
(@dialogue_id, 'Volunteer', 'Y a-t-il une formation obligatoire avant le festival ?', 11),
(@dialogue_id, 'Coordinator', 'Oui, une réunion d\'information et de formation est prévue le 8 août au soir.', 12),
(@dialogue_id, 'Volunteer', 'Comment les horaires sont-ils attribués ? Peut-on choisir nos postes ?', 13),
(@dialogue_id, 'Coordinator', 'Vous pouvez nous donner vos préférences pour les postes et les horaires, et nous faisons de notre mieux pour accommoder tout le monde.', 14),
(@dialogue_id, 'Volunteer', 'Recevons-nous un T-shirt ou un uniforme du festival ?', 15),
(@dialogue_id, 'Coordinator', 'Oui, chaque bénévole reçoit un T-shirt officiel du festival à porter pendant ses heures de travail.', 16);
