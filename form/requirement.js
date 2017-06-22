function fieldCheck() {
        this.fields = new Array();
        this.expressions = new Array();
        this.submitButton = null;
        this.varName = "";
        this.formObject = null;

        this.init = function(vn){this.varName = vn;};

        this.addField = function(fieldObj, expression){
                this.fields.push(fieldObj);
                var expressionsID = this.expressions.push(expression);
                fieldObj.setAttribute("onchange",this.varName+".checkAll()");
                //fieldObj.setAttribute("onkeypress","return "+this.varName+".checkSingleObj("+expressionsID+",event);");
        };

        this.checkSingleObj = function(fieldID, e) {
                if (!e) { var e = window.event }
                if (e.keyCode) { code = e.keyCode; } else if (e.which) { code = e.which; }
                var character = String.fromCharCode(code);
                // if they pressed esc... remove focus from field...
                if (code == 27) { this.blur(); return false; }
                // ignore if they are press other keys
                // strange because code: 39 is the down key AND ' key...
                // and DEL also equals .
                if (!e.ctrlKey && code != 9 && code != 8 && code != 36 && code != 37 && code != 38 && (code != 39 || (code == 39 && character == "'")) && code != 40) {
                        if (this.fields[fieldID].value.match(this.expressions[fieldID])) {
                                return true;
                        } else {
                                return false;
                        }
                }
        };

        this.checkAll = function(){
                var ok = true;
                for(var i = 0; i < this.fields.length;++i){
                        var res = this.fields[i].value.trim();
                        res = res.match(this.expressions[i]);
                        if(res == null || res.length == 0 || res[0] != this.fields[i].value.trim()){
                                ok = false;
                                document.getElementById("id-"+this.fields[i].name).className+=" has-error";
                        }else{
                                document.getElementById("id-"+this.fields[i].name).className="form-group";
                        }
                }
                return ok;
        };

        this.setSubmitButton = function(button){
                this.submitButton = button;
                this.formObject.setAttribute("onsubmit","return "+this.varName+".checkAll()");
        };
};

