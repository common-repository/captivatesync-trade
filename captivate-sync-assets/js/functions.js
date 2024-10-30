function cfmsync_toaster(result, message) {
	var cfmsync_toaster = document.querySelector('.cfm-toaster');

	cfmsync_toaster.style.opacity = "1";

	if (result == 'success') {
		cfmsync_toaster.classList.add("cfm-toast-success");
	}
	else {
		cfmsync_toaster.classList.add("cfm-toast-error");
	}

	cfmsync_toaster.textContent = message;

	setTimeout(function(){
		cfmsync_toaster.style.opacity = "0";
		cfmsync_toaster.classList.remove("cfm-toast-error");
		cfmsync_toaster.classList.remove("cfm-toast-success");
	}, 5000);
}

function cfm_get_url_vars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi,
	function(m,key,value) {
	 	vars[key] = value;
	});
	return vars;
}

function cfm_truncate(str, n){
  return (str.length > n) ? str.substr(0, n-1) + '&hellip;' : str;
};