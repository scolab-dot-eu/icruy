var loginImage = require('../../../assets/images/male-female-icons.png')

function Login(config) {
    this.config = config;
    if (this.config.user) {
        this.renderAuthenticated(this.config.user);
    } else {
        this.renderInitSession();
    }
    
}
   
Login.prototype = {
    renderInitSession: function() {
        var html = '';
        
        html += '<div class="card" style="text-align: center; width: 100%; margin-top: 10px;">';
        html +=     '<img style="width: 200px; margin-left:90px;" class="card-img-top" src="' + loginImage + '" alt="Card image cap">';
        html +=     '<div class="card-body">';
        html +=         '<h5 class="card-title">Inciar sesión</h5>';
        html +=         '<p class="card-text">Si eres un usuario con rol departamental o de admisnitración, puedes inciar sesión para editar los elementos del inventario</p>';
        html +=         '<a id="login-button" href="#" class="btn btn-warning">Iniciar sesión</a>';
        html +=     '</div>';
        html += '</div>';

        $('#toc-profile').append(html);

        $('#login-button').on('click', function() {
            location.href = window.serviceURL + '/viewer_login';
        });
    },

    renderAuthenticated: function(user) {
        var html = '';
        
        html += '<div class="card" style="text-align: center; width: 100%; margin-top: 10px;">';
        html +=     '<img style="width: 200px; margin-left:90px;" class="card-img-top" src="' + loginImage + '" alt="Card image cap">';
        html +=     '<div class="card-body">';
        html +=         '<ul class="list-group list-group-flush">';
        html +=             '<li class="list-group-item"><i style="margin-right: 10px;" class="fa fa-user"></i>' + user.name + '</li>';
        html +=             '<li class="list-group-item"><i style="margin-right: 10px;" class="fa fa-envelope"></i>' + user.email + '</li>';
        html +=         '</ul>'; 
        html +=     '</div>';
        html +=     '<div class="card-footer text-muted">';
        html +=         '<a id="dashboard-button" href="#" class="btn btn-warning m-r-5">Panel de control</a>';
        html +=         '<a id="logout-button" href="#" class="btn btn-warning">Cerrar sesión</a>';
        html +=     '</div>';
        html += '</div>';

        $('#toc-profile').append(html);

        $('#dashboard-button').on('click', function() {
            location.href = window.serviceURL + '/home';
        });

        $('#logout-button').on('click', function() {
            $.ajax({
                url: window.serviceURL + '/api/logout',
                type: 'POST',
                async: false
        
            }).
            done(function(resp) {})
            .fail(function(error) {});
        });
    }
}
   
module.exports = Login;