<html>
<head>
<title>Importar excel a mysql</title>
</head>
  <body>
 
    <?php echo form_open_multipart('import/to_mysql');?>
 
      <input type="file" name="excel" size="20" />
 
      <br /><br />
 
      <label>Escribe el nombre de la tabla.</label><br />
      <input type="text" name="table" /><br /><br />
 
      <label>Escribe, separados por una coma, los campos de tu tabla excepto los autoincrementales, ejemplo id etc.</label><br />
      <input type="text" name="fields" style="width:600px" />
 
      <input type="submit" value="Importar" />
 
  
 
<input type="file" name="xlfile" id="xlf" /> 

  <input type="hidden" name="useworker" checked>
 <input type="hidden" name="userabs" checked>
 
 
<div id="htmlout"></div>
<br />
<!-- uncomment the next line here and in xlsxworker.js for encoding support -->



<script src="<?php echo base_url();?>assets/js/xlsx.js"></script>
<script>
/*jshint browser:true */
/* eslint-env browser */
/*global Uint8Array, Uint16Array, ArrayBuffer */
/*global XLSX */
var X = XLSX;
var XW = {
	/* worker message */
	msg: 'xlsx',
	/* worker scripts */
	worker: '<?php echo base_url();?>/assets/js/xlsxworker.js'
};

var global_wb;

var process_wb = (function() {
 
	var HTMLOUT = document.getElementById('htmlout');

	var get_format = (function() {
		var radios = document.getElementsByName( "format" );
		return function() {
			for(var i = 0; i < radios.length; ++i) if(radios[i].checked || radios.length === 1) return radios[i].value;
		};
	})();

	var to_json = function to_json(workbook) {
		var result = {};
		workbook.SheetNames.forEach(function(sheetName) {
			var roa = X.utils.sheet_to_json(workbook.Sheets[sheetName]);
			if(roa.length) result[sheetName] = roa;
		});
		return JSON.stringify(result, 2, 2);
	};

	var to_csv = function to_csv(workbook) {
		var result = [];
		workbook.SheetNames.forEach(function(sheetName) {
			var csv = X.utils.sheet_to_csv(workbook.Sheets[sheetName]);
			if(csv.length){
				result.push("SHEET: " + sheetName);
				result.push("");
				result.push(csv);
			}
		});
		return result.join("\n");
	};

	var to_fmla = function to_fmla(workbook) {
		var result = [];
		workbook.SheetNames.forEach(function(sheetName) {
			var formulae = X.utils.get_formulae(workbook.Sheets[sheetName]);
			if(formulae.length){
				result.push("SHEET: " + sheetName);
				result.push("");
				result.push(formulae.join("\n"));
			}
		});
		return result.join("\n");
	};

	var to_html = function to_html(workbook) {
		HTMLOUT.innerHTML = "";
		workbook.SheetNames.forEach(function(sheetName) {
			var htmlstr = X.write(workbook, {sheet:sheetName, type:'binary', bookType:'html'});
			HTMLOUT.innerHTML += htmlstr;
		});
		return "";
	};

	return function process_wb(wb) {
		global_wb = wb;
		var output = ""; 
		 output = to_html(wb); 
		 
	};
})();

//var setfmt = window.setfmt = function setfmt() { if(global_wb) process_wb(global_wb); };

 

var do_file = (function() {
	var rABS = typeof FileReader !== "undefined" && (FileReader.prototype||{}).readAsBinaryString;
	var domrabs = document.getElementsByName("userabs")[0];
	if(!rABS) domrabs.disabled = !(domrabs.checked = false);

	var use_worker = typeof Worker !== 'undefined';
	var domwork = document.getElementsByName("useworker")[0];
	if(!use_worker) domwork.disabled = !(domwork.checked = false);

	var xw = function xw(data, cb) {
		var worker = new Worker(XW.worker);
		worker.onmessage = function(e) {
			switch(e.data.t) {
				case 'ready': break;
				case 'e': console.error(e.data.d); break;
				case XW.msg: cb(JSON.parse(e.data.d)); break;
			}
		};
		worker.postMessage({d:data,b:rABS?'binary':'array'});
	};

	return function do_file(files) {
		rABS = domrabs.checked;
		use_worker = domwork.checked;
		var f = files[0];
		var reader = new FileReader();
		reader.onload = function(e) {
			if(typeof console !== 'undefined') console.log("onload", new Date(), rABS, use_worker);
			var data = e.target.result;
			if(!rABS) data = new Uint8Array(data);
			if(use_worker) xw(data, process_wb);
			else process_wb(X.read(data, {type: rABS ? 'binary' : 'array'}));
		};
		if(rABS) reader.readAsBinaryString(f);
		else reader.readAsArrayBuffer(f);
	};
})();
 
(function() {
	var xlf = document.getElementById('xlf');
	if(!xlf.addEventListener) return;
	function handleFile(e) { do_file(e.target.files); }
	xlf.addEventListener('change', handleFile, false);
})();
</script>
    <?php echo form_close() ?>
 
  </body>
</html>
