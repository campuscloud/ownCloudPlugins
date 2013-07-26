	
	$(document).ready(function() {
		//Nur intenen Zugriff erlauben
		if(/(public)\.php/i.exec(window.location.href)!=null) return; // escape when the requested file is public.php
		//Bild für Button laden
		var img = OC.imagePath('core','actions/play');
		//FileActions registrieren
		if (typeof FileActions !== 'undefined') {
			FileActions.register('all', t('group_share','ShareCrm'),OC.PERMISSION_READ, function(){return OC.imagePath('core','actions/play')}, function(file) {
				if (($('#shareDrop').length > 0)) {
					$('#shareDrop').detach();
				}
				else{
					shareCreateUI(true,file,false);
				}
			});
		};


		function button(file,copy){
				}
		//Share Button erstellen
		$('<a class="sharecrm" id="sharecrm" href="#"><img class="svg" src="'+img+'" alt="'+t('group_share','ShareCrm')+'">'+t('group_share','ShareCrm')+'</a>').appendTo('#headerName .selectedActions');

		//Share button click event
		//
		$('#sharecrm').click(function(event){
			if($('#shareDrop').length>0){
				//ShareDrop löschen/ausblenden, wenn vorhanden
				$('#shareDrop').detach();
				return;
			}
			//event.preventDefault();
			event.stopPropagation();

			// Wenn shareDrop Data vorhanden mache ...
			if ($('#shareDrop').data('item-type') !== undefined && $('#shareDrop').data('item') !== undefined) {
				alert('shareForm Drop vars einlesen');
				var itemType = ("#shareDrop").parent().parent().attr("data-type");
				var itemSource = $('#shareDrop').data('item');
				var appendTo = $('#shareDrop').parent().parent();
				var link = false;
				var possiblePermissions = $(this).data('possible-permissions');
				if ($('#shareDrop').data('link') !== undefined && $('#shareDrop').data('link') == true) {
					link = true;
				}
			}

			/**var files = getSelectedFiles('name');
			var file='';
			for( var i=0;i<files.length;++i){
				file += files[i]+';';
			}**/
			shareCreateUI(false,file,false);
		});
		/**$(this).click(function(event){
			if( (!($(event.target).hasClass('ui-corner-all')) && $(event.target).parents().index($('.ui-menu'))==-1) &&
				(!($(event.target).hasClass('shareUI')) && $(event.target).parents().index($('#shareDrop'))==-1)){
				$('#shareDrop').detach();
			}
		});*/
		$('#shareForm').live('submit',function(){
			var crmId = $('#crmId').val();
			var user = $('#user').val();
			var password = $('#password').val();
			//var file = $('#dirFile').val();
			var dir  = $('#dir').val();

			var itemType = $("#shareDrop").parent().parent().attr("data-type");
			var itemSource = $('#shareDrop').data('item-source');
			var file = $("#shareDrop").parent().parent().attr("data-id");


			//alert('shareForm sent');
			$.ajax({
				type: 'POST',
				url: OC.linkTo('group_share','ajax/sharecrm.php'),
				cache: false,
				data: {itemType: itemType, itemSource: itemSource, dir: dir, src: file, crmId: crmId, user: user, password: password},
				success: function(data){
					if(data.status=="success"){
					$.each(data.name,function(index,value){
						FileList.remove(value);
						procesSelection();
						});
					}
				}
			});
			$('#dirList').autocomplete("close");
			$('#shareDrop').detach();
			return false;
		});
	});
	/**
	 * draw the share-dialog; if file is readonly, activate copy
	 *
	 * @local - true for single file, false for global use
	 * @file - filename in the local directory
	 */
	function shareCreateUI(local,file){
		file2 = file.split(';');
		var permUpdate = true;
		for(var i=0;i<file2.length;++i){
			if(file2[i]== "") continue;
			var tmp = $('tr[data-file="'+file2[i]+'"]');
			if((OC.PERMISSION_UPDATE&parseInt(tmp.attr('data-permissions')))==0){ // keine updaterechte
				permUpdate=false;
				break;
			}
		}
		var html = '<div id="shareDrop" class="shareUI">';
		html += '<form action="#" id="shareForm">';
		html += '<input type="text" id="user" size="7" value="" />';
		html += '<label for="crmId">'+t('group_share','User')+'</label><br>';
		html += '<input type="password" id="password" size="7" value="" />';
		html += '<label for="crmId">'+t('group_share','Password')+'</label><br>';
		html += '<input type="text" id="crmId" size="7" value="" />';
		html += '<label for="crmId">'+t('group_share','Id')+'</label><br>';
		html += '<input type="hidden" id="dirFile" value="'+file+'" />';
		html += '<input type="submit" id="dirListSend" value="'+t('group_share','Share')+'" />';
		html += '<strong id="shareWarning"></strong></form>';
		html += '</div>';
		if(local){
			$(html).appendTo($('tr').filterAttr('data-file',file).find('td.filename'));
		}
		else{
			$(html).addClass('share').appendTo('#headerName .selectedActions');
		}
	}