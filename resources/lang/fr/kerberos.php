<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    'notif' => [
        'unknown_attempt' => [
            'subject' => '⚠️ Tentative de connexion Kerberos inconnue - :app',
            'greeting' => 'Identifiant Kerberos non reconnu détecté',
            'line_detected' => "Une tentative d'authentification a été effectuée avec un identifiant Kerberos inconnu.",
            'line_legit' => "S'il s'agit d'un utilisateur légitime, ajoutez son identifiant Kerberos dans le système.",
            'salutation' => '— :app',
        ],
        'new_request' => [
            'subject' => "📋 Nouvelle demande d'accès - :app",
            'greeting' => "Nouvelle demande d'accès reçue",
            'line_details' => "Un utilisateur a demandé l'accès à l'application.",
        ],
        'rejected' => [
            'subject' => "❌ Demande d'accès refusée - :app",
            'greeting' => "Votre demande d'accès a été refusée.",
            'line_reason' => 'Motif : :reason',
            'line_retry' => 'Vous pouvez soumettre une nouvelle demande avec une justification complémentaire si nécessaire.',
            'action' => 'Soumettre une nouvelle demande',
        ],
        'accepted' => [
            'subject' => "✅ Demande d'accès approuvée - :app",
            'greeting' => "Votre demande d'accès a été approuvée !",
            'line_role' => 'Rôle attribué : **:role**',
            'line_message' => "Message de l'administrateur : :message",
            'action' => 'Se connecter',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages flash
    |--------------------------------------------------------------------------
    */

    'flash' => [
        'error_empty_kerberos' => 'Veuillez saisir ou sélectionner un identifiant Kerberos.',
        'simulation_disabled' => 'Simulation désactivée.',
        'error_generic' => 'Une erreur est survenue. Veuillez réessayer.',
        'simulation_banner_logout' => 'Simulation désactivée. Veuillez vous reconnecter.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    'validation' => [
        'justification_required' => "Vous devez fournir une justification pour votre demande d'accès.",
        'justification_min' => 'La justification doit contenir au moins 20 caractères.',
        'justification_max' => 'La justification ne peut pas dépasser 500 caractères.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vues — Simulation Kerberos
    |--------------------------------------------------------------------------
    */

    'simulate' => [
        'dev_mode' => '⚠️ Mode Développement',
        'env_info' => 'Simulation Kerberos active (environnement :env)',
        'active_label' => 'Simulation en cours',
        'disable' => 'Désactiver',
        'enable_section' => 'Activer la simulation',
        'custom_label' => 'Identifiant Kerberos personnalisé',
        'custom_placeholder' => 'prenom.nom@exemple.fr',
        'custom_hint' => "Saisissez n'importe quel identifiant Kerberos",
        'or' => 'ou',
        'select_label' => 'Sélectionner un utilisateur existant',
        'select_placeholder' => 'Choisir un identifiant existant...',
        'select_hint' => 'Les 10 premiers identifiants de la base de données',
        'simulate_button' => 'Simuler la connexion',
        'simulating' => 'Connexion en cours...',
        'warning' => '<strong>Attention :</strong> Ce mode de simulation est <strong>strictement réservé aux environnements de développement et de pré-production</strong>. Il est automatiquement désactivé en production.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vues — Accès refusé
    |--------------------------------------------------------------------------
    */

    'access_denied' => [
        'title' => 'Accès refusé',
        'subtitle' => "Votre identifiant Kerberos n'est pas reconnu",
        'unknown_id' => 'Identifiant non reconnu',
        'not_registered' => "L'identifiant Kerberos suivant n'est pas enregistré dans notre système :",
        'admins_notified' => "Les administrateurs ont été automatiquement notifiés de cette tentative de connexion. Si vous pensez qu'il s'agit d'une erreur, veuillez contacter votre service informatique.",
        'what_to_do' => 'Que faire ?',
        'tip_network' => "Assurez-vous d'être connecté au réseau de l'entreprise",
        'tip_it' => 'Contactez votre service informatique pour vérifier votre compte',
        'tip_local' => 'Utilisez le formulaire de connexion classique si vous disposez d\'un compte local',
        'back_button' => 'Retour à la page de connexion',
        'attempt_time' => 'Tentative le :datetime',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vues — Demande d'accès
    |--------------------------------------------------------------------------
    */

    'request_access' => [
        'title' => "Demande d'accès",
        'subtitle' => "Votre compte n'a pas encore de rôle attribué. Veuillez remplir ce formulaire.",
        'sent_title' => "Demande d'accès envoyée",
        'sent_subtitle' => 'Votre demande a été transmise aux administrateurs',
        'sent_body' => "Votre demande d'accès a bien été envoyée aux administrateurs.",
        'sent_notification' => 'Vous serez notifié par email dès que votre demande aura été traitée.',
        'no_role_title' => 'Compte sans rôle',
        'no_role_body' => "Votre identifiant Kerberos <strong>:kerberos</strong> est reconnu, mais votre compte n'a aucun rôle attribué. Veuillez justifier votre demande d'accès ci-dessous.",
        'kerberos_label' => 'Identifiant Kerberos',
        'kerberos_hint' => 'Votre identifiant Kerberos détecté automatiquement',
        'justification_label' => 'Justification de votre demande',
        'justification_placeholder' => "Expliquez pourquoi vous avez besoin d'accéder à l'application (minimum 20 caractères)...",
        'justification_hint' => 'Minimum 20 caractères, maximum 500 caractères',
        'admin_info' => 'Les administrateurs recevront votre demande par email et vous serez notifié une fois celle-ci traitée.',
        'submit_button' => "Envoyer la demande d'accès",
        'submitting' => 'Envoi en cours...',
        'cancel_button' => 'Retour à la connexion',
        'back_button' => 'Retour à la connexion',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vues — Bandeau simulation
    |--------------------------------------------------------------------------
    */

    'simulation_banner' => [
        'mode' => 'Mode Simulation',
        'quit' => 'Quitter',
        'description' => 'Vous êtes connecté en mode simulation (:env). Cliquez sur "Quitter" pour vous déconnecter et désactiver la simulation.',
        'disable_title' => 'Désactiver la simulation',
    ],

];
