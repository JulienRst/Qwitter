<?php 
	echo('
		<div class="qwitt">
			<div class="qwitt-header">
				<div class="q-header-pic">
					<div style="background-image:url(datas/profil-pic/'.$profil["url_pic"].');"></div>
				</div>
				<div class="q-header-view">
					<p class="q-header-view-name">'.$profil["surname"].' '.$profil["name"].'</p>
					<p class="q-header-view-date">'.$date.'</p>
				</div>
				<div class="q-header-icon">
					<div class="icon-reqwitt"></div>
					<div class="icon-fav"></div>							
				</div>
			</div>
			<div class="qwitt-message">
				<p>
					'.$reqwitt_ofmessage.'
				</p>
			</div>
			<div class="sprt"></div>
			<div class="rq">
					<div class="rq-header">
						<div class="rq-header-pic">
							<div style="background-image:url(datas/profil-pic/'.$reqwitt_pic.');"></div>
						</div>
						<div class="rq-header-view">
							<p class="rq-header-view-name">'.$reqwitt_name.'</p>
							<p class="rq-header-view-date">'.$reqwitt_date.'</p>
						</div>
					</div>
					<div class="rq-message">
						<p>
							'.$reqwitt_message.'
						</p>
					</div>
				</div>
			<div class="qwitt-footer">
				<img src="datas/img/star.png">
				<p>'.$nbFav.' favoris</p>
				<img src="datas/img/small_reqwitt.png">
				<p>'.$nbReq.' reqwitts</p>
			</div>
		</div>
	');
?>