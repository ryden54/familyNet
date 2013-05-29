<div class="navbar">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse"> <span class="icon-bar"></span> <span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a> <a class="brand" href="/"><?=$context->getTitle(); ?></a>
			<?php
if ($context->getUser() instanceof User) {
			?>
			<div class="nav-collapse collapse">
				<ul class="nav">
					<?php
	function displayMenu($item) {

		$res = '';
		if ($item === false) {
			$res .= '<li class="divider"></li>';
		} else {

			if ($item[3] !== false) {
				if (Context::getInstance()->getUser()->hasAuth($item[3]) === false) {
					return '';
				}
			}

			$subMenus = '';
			if (is_array($item[2]) === true) {
				foreach ($item[2] As $s) {
					$subMenus .= displayMenu($s);
				}
			}

			$classes = array();

			if ($item[1] !== false && stripos($_SERVER['REQUEST_URI'], $item[1]) !== false && $item[1] !== '/') {
				$classes[] = 'active';
			}

			if (strlen(trim($subMenus)) > 0) {
				$classes[] = 'dropdown';
				$res .=
						'<li class="' . implode(' ', $classes) . '"><a ' . ($item[1] !== false ? ' href="' . $item[1] . '"' : '')
								. ' class="dropdown-toggle"
						data-toggle="dropdown">' . $item[0] . ' <b class="caret"></b></a>
						<ul class="dropdown-menu">' . $subMenus . '</ul></li>';
			} else {
				if ($item[1] === false) {
					$res .= '<li class="nav-header">' . $item[0] . '</li>';

				} else {
					$res .=
							'<li class="' . implode(' ', $classes) . '"><a ' . ($item[1] !== false ? ' href="' . $item[1] . '"' : '') . '>' . $item[0]
									. '</a></li>';
				}
			}
		}
		return $res;

	}

	foreach (Config::get('MENU', 'menus') As $item) {
		echo displayMenu($item);
	}

					?>
				</ul>
				<form class="navbar-search pull-right form-search" action="/search.php" method="GET">
					<div class="input-append">
						<input type="text" class="search-query" placeholder="Rechercher" name="q" required="required"
							value="<?=Html::getRequestOrPost('q', '', Html::TEXT); ?>">
						<button class="btn" type="submit">
							<i class="icon-search"></i>
						</button>
					</div>
					<input type="hidden" name="t" value="<?=$context->getInstance()->getUniverse(); ?>" />
				</form>
			</div>
			<!--/.nav-collapse -->
			<?php
	/*

	                    <li class="active"><a href="/">Accueil</a></li>
	                    <li class="dropdown"><a href="#" class="dropdown-toggle"
	                        data-toggle="dropdown">Famille <b class="caret"></b></a>
	                        <ul class="dropdown-menu">
	                            <li><a href="/membres.php">Membres</a></li>
	                            <li><a href="/genealogie.php">G�n�alogie</a></li>
	                        </ul></li>
	                    <li><a href="/photos.php">Photos</a></li>
	                    <li><a href="/discussions.php">Discussions</a></li>

	    if ($context->getUser()->hasAuth('Gestion') === true) {

	                    <li class="dropdown open"><a href="#" class="dropdown-toggle"
	                        data-toggle="dropdown">Gestion <b class="caret"></b></a>
	                        <ul class="dropdown-menu">
	                            <li><a href="#">Listes administrables</a></li>
	                            <li><a href="#">Page d'accueil</a></li>
	                            <li><a href="#">Newsletters</a></li>
	                            <li class="divider"></li>
	                            <li class="nav-header">Statistiques</li>
	                            <li><a href="#">Visiteurs</a></li>
	                            <li><a href="#">Connexions</a></li>
	                        </ul></li>

	    }

	                    <li><a href="/?logout=1">D�connexion</a></li>

	                </ul>
	            </div>
	            <!--/.nav-collapse -->
	 */
}

			?>
		</div>
	</div>
</div>