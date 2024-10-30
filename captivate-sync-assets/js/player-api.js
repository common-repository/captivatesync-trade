var cps=[];
function CP( iframe ) {
	if ( typeof( iframe ) == 'string' ) {
		iframe = document.querySelector( iframe );
	}

	this._iframe = iframe;
	cps.push(this);
	this.eventListeners={};
}
function searchInCPs(win){
	for(var i in cps){
		if(cps[i]._iframe.contentWindow==win){
			return i;
		}
	}
	return-1;
}

CP.prototype._post=function(msg){
	this._iframe.contentWindow.postMessage(msg,'*');
};
CP.prototype.play=function(){
	this._post({ action:"CP.API.PLAY" });
};
CP.prototype.pause=function(){
	this._post({ action:"CP.API.PAUSE" });
};
CP.prototype.toggle=function(){
	this._post({ action:"CP.API.TOGGLE" });
};
CP.prototype.seekTo=function(millisecond){
	this._post({ action:"CP.API.SEEK_TO", value:millisecond });
};
CP.prototype.getVolume=function(callback){
	this._getter('GET_VOLUME',callback);
};
CP.prototype.getDuration=function(callback){
	this._getter('GET_DURATION',callback);
};
CP.prototype.getPosition=function(callback){
	this._getter('GET_POSITION',callback);
};
CP.prototype.getPaused=function(callback){
	this._getter('GET_PAUSED',callback);
};
CP.prototype.getSource=function(callback){
	this._getter('GET_SOURCE',callback);
};
CP.prototype._getter=function(eventName,callback){
	var self=this;
	var cp=function(event){
		if(callback) {
			callback(event.data);
		}
		self.unbind('CP.API.CALLBACK.'+eventName,cp);
	}
	this.bind('CP.API.CALLBACK.'+eventName,cp);
	this._post({
		action:'CP.API.'+eventName
	});
};
CP.prototype.bind=function(event,callback){
	if(!(this.eventListeners[event]instanceof Array)){
		this.eventListeners[event]=[];
	}
	this.eventListeners[event].push(callback);this._iframe.addEventListener(event,callback,false);
};
CP.prototype.unbind=function(event,callback){
	if(callback){
		var index=this.eventListeners[event].indexOf(callback);
		this._iframe.removeEventListener(event,callback,false);if(index!==-1){
			this.eventListeners[event].pop(index);
		}
	}
	else{
		if(this.eventListeners[event]instanceof Array){
			for(var i in this.eventListeners[event]){
				this._iframe.removeEventListener(event,this.eventListeners[event][i],false);
			}
			this.eventListeners[event]=[];
		}
	}
};
window.addEventListener('message',function(event){
	if(event.data.event&&event.data.event.search('CP.')!=-1){
		var index=searchInCPs(event.source);
		if(index!=-1){
			var e=new Event(event.data.event);
			e.data=event.data.data;
			cps[index]._iframe.dispatchEvent(e);
		}
	}
});