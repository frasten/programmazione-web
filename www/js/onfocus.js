/* CONFIG */menuClassName = "menuNavigazione";/* SCRIPT */function closeSub(menu) {	for (var i=0; i<menu.childNodes.length; i++)		if (menu.childNodes[i].nodeName.toLowerCase()=="li") {			li = menu.childNodes[i];			li.onmouseover = li.onactivate = li.onfocus = function() { if (this.subMenu) this.subMenu.className = this.subMenu.className.replace(/subMenu-off/g,"subMenu-on") };			li.onmouseout = li.ondeactivate = li.onblur = function() { if (this.subMenu) closeSub(this.subMenu) };			for (j=0; j<li.childNodes.length; j++)				if (li.childNodes[j].nodeName.toLowerCase()=="ul" || li.childNodes[j].nodeName.toLowerCase()=="ol") closeSub(li.subMenu = li.childNodes[j]);		}	menu.className = menu.className.replace(/\s?subMenu-on/g,"")+" subMenu-off";}/* ON LOAD */window.onload = function(e) {	if(tags_ = document.getElementsByTagName('ul'))		for(i=0; i<tags_.length; i++) 			if (tags_[i].className==menuClassName) closeSub(tags_[i]);	if(tags_ = document.getElementsByTagName('ol'))		for(i=0; i<tags_.length; i++) 			if (tags_[i].className==menuClassName) closeSub(tags_[i]);	/* per il menu' che rimane attivo */	nomeSezione = document.getElementById('body').className;/*	alert(nomeSezione); */	voce = document.getElementById(nomeSezione);	if ( ! voce ) return;/*	alert(voce.id); */	voce.id = "attivo";/*	alert(voce.id); */}