function OpenUrl()
{
	
}

// Disable button toggling.
OpenUrl.prototype.GetState = function()
{
	return FCK_TRISTATE_OFF;
}

// Our method which is called on button click.


function _GET( name )
{
	var regexS = "[\\?&]"+name+"=([^&#]*)";
	var regex = new RegExp ( regexS );
	var tmpURL = window.location.href;
	var results = regex.exec( tmpURL );
	if( results == null )
		return"";
	else
		return results[1];
}

OpenUrl.prototype.Execute = function(x)
{
	window.parent.mostrarVentanaImg(_GET('InstanceName'));
}

var botonPrueba=new OpenUrl();

FCKCommands.RegisterCommand('openurl', botonPrueba);
    // Add the button.
var item = new FCKToolbarButton('openurl', 'Agregar Imagen');
item.IconPath = FCKPlugins.Items['openurl'].Path + 'upload.png';
FCKToolbarItems.RegisterItem('openurl', item);

