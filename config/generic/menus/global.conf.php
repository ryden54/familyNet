<?php
$config['MENU'] =
		array(
				// 				array(
				// 					'Accueil', '/', false, false
				// 				),
				array(
						'Famille',
						false,
						array(
								array(
									'Membres', '/famille/index.php', false, false
								),
								array(
									'Généalogie', '/famille/genealogie.php', false, false
								),
								array(
									'Listes', '/famille/listing.php', false, false
								),
						),
						false
				),
				array(
					'Photos', '/photos/', false, false
				),
				array(
					'Discussions', '/discussions/', false, false
				),
				array(
						'Gestion',
						false,
						array(
							// 								array(
							// 									"Page d'accueil", '/gestion/home.php', false, false
							// 								),
							array(
								'Newsletters', '/gestion/newsletters.php', false, false
							),
														false,
														array(
															'Mots de passe', '/gestion/passwords.php', false, false
														),
						// 								array(
						// 									"Visiteurs", '/gestion/stats/users.php', false, false
						// 								),
						// 								array(
						// 									'Connexions', '/gestion/stats/logins.php', false, false
						// 								),
						),
						'Gestion'
				),
				array(
					'Déconnexion', '?logout=1', false, false
				),
		);
