function Utils(config) {
    this.config = config;
    this.departamento = null;
    this.initialize();
}
   
Utils.prototype = {
    initialize: function() {
        var searchParams = new URLSearchParams(window.location.search);

        if (searchParams.has('departamento')) {
            this.departamento = searchParams.get('departamento');
        }
     },

    getAttributeInput: function(fields, key, value){

        var html = '';

        for (i in fields) {
            if (fields[i].name == key) {
                if (fields[i].type == 'string') {
                    if (fields[i].name == 'departamento' || fields[i].name == 'status') {
                        html += '<div class="form-group">';
                        html +=     '<label for="' + key + '">' + fields[i].label + '</label>';
                        html +=     '<input readOnly style="font-size: 12px;" type="text" class="form-control" name="' + key + '" id="' + key + '" value="' + value + '">';
                        html += '</div>';

                    } else {
                        html += '<div class="form-group">';
                        html +=     '<label for="' + key + '">' + fields[i].label + '</label>';
                        html +=     '<input style="font-size: 12px;" type="text" class="form-control" name="' + key + '" id="' + key + '" value="' + value + '">';
                        html += '</div>';
                    }
                    

                } else if (fields[i].type == 'stringdomain') {
                    html += '<div class="form-group">';
                    html +=     '<label for="' + key + '">' + fields[i].label + '</label>';
                    html +=     '<select style="font-size: 12px;" class="form-control" name="' + key + '" id="' + key + '">';
                    for (j in fields[i].domain) {
                        if (fields[i].domain[j].code == value) {
                            html += '<option selected value="' + fields[i].domain[j].code + '">' + fields[i].domain[j].definition + '</option>';
                        } else {
                            html += '<option value="' + fields[i].domain[j].code + '">' + fields[i].domain[j].definition + '</option>';                           
                        }
                    }
                    html +=     '</select>';
                    html += '</div>';
                    
                } else if (fields[i].type == 'intdecimal') {
                    html += '<div class="form-group">';
                    html +=     '<label for="' + key + '">' + fields[i].label + '</label>';
                    if (fields[i].name == 'id') {
                        html += '<input readOnly style="font-size: 12px;" type="number" class="form-control" name="' + key + '" id="' + key + '" value="' + value + '">';
                    } else {
                        html += '<input style="font-size: 12px;" type="number" class="form-control" name="' + key + '" id="' + key + '" value="' + value + '">';
                    }
                    html += '</div>';

                } else if (fields[i].type == 'decimal' || fields[i].type == 'double') {
                    html += '<div class="form-group">';
                    html +=     '<label for="' + key + '">' + fields[i].label + '</label>';
                    html +=     '<input style="font-size: 12px;" type="number" step="0.01" class="form-control" name="' + key + '" id="' + key + '" value="' + value + '">';
                    html += '</div>';

                } else if (fields[i].type == 'date') {
                    var current = value.substring(0, value.length - 1);
                    var arrayDate = current.split('-');
                    var year = arrayDate[0];
                    var month = arrayDate[1];
                    //var day = arrayDate[2];
                    var intDay = parseInt(arrayDate[2]) + 1;
                    var day = intDay.toString();
                    var date = year + '-' + month + '-' + day;
                    html += '<div class="form-group">';
                    html +=     '<label for="' + key + '">' + fields[i].label + '</label>';
                    html +=     '<input type="date" name="' + key + '" id="' + key + '" min="1000-01-01" max="3000-12-31" value="' + date + '" class="form-control">';
                    html += '</div>';

                }
            }
        }

        return html;
        
    },

    getAttributeEmptyInput: function(field){

        var html = '';

        if (field.type == 'string') {
            if (field.name == 'departamento') {
                html += '<div class="form-group">';
                html +=     '<label for="' + field.name + '">' + field.label + '</label>';
                html +=     '<input readOnly style="font-size: 12px;" type="text" class="form-control" value="' + this.departamento + '" name="' + field.name + '" id="' + field.name + '">';
                html += '</div>';

            } else if (field.name == 'status') {
                html += '<div class="form-group">';
                html +=     '<label for="' + field.name + '">' + field.label + '</label>';
                html +=     '<input readOnly style="font-size: 12px;" type="text" class="form-control" value="VALIDADO" name="' + field.name + '" id="' + field.name + '">';
                html += '</div>';

            } else {
                html += '<div class="form-group">';
                html +=     '<label for="' + field.name + '">' + field.label + '</label>';
                html +=     '<input style="font-size: 12px;" type="text" class="form-control" name="' + field.name + '" id="' + field.name + '">';
                html += '</div>';
            }
            

        } else if (field.type == 'stringdomain') {
            html += '<div class="form-group">';
            html +=     '<label for="' + field.name + '">' + field.label + '</label>';
            html +=     '<select style="font-size: 12px;" class="form-control" name="' + field.name + '" id="' + field.name + '">';

            for (i in field.domain) {
                if (field.domain[i].code == this.departamento) {
                    html += '<option selected value="' + field.domain[i].code + '">' + field.domain[i].definition + '</option>';
                } else {
                    html += '<option value="' + field.domain[i].code + '">' + field.domain[i].definition + '</option>';                           
                }
            }

            html +=     '</select>';
            html += '</div>';
                
        } else if (field.type == 'intdecimal') {
            html += '<div class="form-group">';
            html +=     '<label for="' + field.name + '">' + field.label + '</label>';
            html +=     '<input style="font-size: 12px;" type="number" class="form-control" name="' + field.name + '" id="' + field.name + '">';
            html += '</div>';

        } else if (field.type == 'decimal' || field.type == 'double') {
            html += '<div class="form-group">';
            html +=     '<label for="' + field.name + '">' + field.label + '</label>';
            html +=     '<input style="font-size: 12px;" type="number" step="0.01" class="form-control" name="' + field.name + '" id="' + field.name + '">';
            html += '</div>';

        } else if (field.type == 'date') {
            html += '<div class="form-group">';
            html +=     '<label for="' + field.name + '">' + field.label + '</label>';
            html +=     '<input type="date" name="' + field.name + '" id="' + field.name + '" min="1000-01-01" max="3000-12-31" class="form-control">';
            html += '</div>';

        }

        return html;   
    }
}
   
module.exports = Utils;