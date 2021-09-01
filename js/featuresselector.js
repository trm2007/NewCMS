"use strict";
function FeaturesSelectorConfig(StartGroupId, PricePrefix)
{
    return {
        CloseClassName: "CloseButton",
        OpenClassName: "OpenButton",
        GetFeaturesURL: "/Ajax/get-features-list",
        DivNameId: "FeaturesSelector",
        IdFieldName: "ID_group",
        AllFeatures: [],
        Selected: [],
        StartGroupId: StartGroupId, 
        PricePrefix: PricePrefix,
        addToSelected: function(id, value)
        {
            for(var i=0; i<this.Selected.length; i++)
            {
                // если такое значение для характеристики с id уже есть,
                // то прекращаем выполнение
                if( this.Selected[i].id.toString() === id.toString() 
                    && 
                    this.Selected[i].value.toString() === value.toString() )
                {
                    return;
                }
            }
            this.Selected.push({
                    id: id,
                    value: value
                });
        },
        removeFromSelected: function(id, value)
        {
            for(var i=0; i<this.Selected.length; i++)
            {
                // если такое значение для характеристики с id есть в масииве,
                // то вырезаем и прекращаем выполнение
                if( this.Selected[i].id.toString() === id.toString() 
                    && 
                    this.Selected[i].value.toString() === value.toString() )
                {
                    this.Selected.splice(i, 1);
                    return;
                }
            }
        },
        getTranslitForId: function(id)
        {
            for( var i=0; i<this.AllFeatures.length; i++ )
            {
                if( this.AllFeatures[i].ID_Feature.toString() === id.toString() )
                {
                    return this.AllFeatures[i].FeaturesTranslit;
                }
            }
            return null;
        }
    };
}


function FeaturesSelector(LocalSelectorConfig)
{
    var instance = this;

    this.JSONfinal = function(str, StatusCode, StatusText)
    {
        if( !checkAndAlertStatus(StatusCode, StatusText) ) { return; }
        var JSONMenu = JSON.parse(str);
        
        var UlNode = instance.generateUlNew(JSONMenu);
        if( !UlNode ) { return; }
        var AJAXDiv = document.createElement('div');
        
        //addClass(AJAXDiv, "SelectorTreeDiv");
        AJAXDiv.appendChild( UlNode );
        
        var Button = document.createElement('button');
        Button.appendChild(document.createTextNode("Выбрать"));
        Button.onclick = instance.sendSelectorRequest; //this.sendSelectorRequest.bind(this);
        AJAXDiv.appendChild(Button);
        
        var ContainerDiv = document.getElementById(LocalSelectorConfig.DivNameId);
        ContainerDiv.appendChild(AJAXDiv);
        ContainerDiv.style.display = "block";
//        ContainerDiv.innerHTML = "";
    };

    this.fetchJSONTree = function()
    {
        sendRequest( LocalSelectorConfig.GetFeaturesURL, 
                "POST", 
                JSON.stringify( {
                    GroupId: LocalSelectorConfig.StartGroupId, 
                    URL: document.location.pathname
                } ), 
                this.JSONfinal,
                this );
    };

    this.clickButton = function(e)
    {
        // не можем применять LocalSelectorConfig.CloseClassName, так как во время нажатия 
        // контекст будет совсем другой
//        switchClass(e.target, LocalSelectorConfig.CloseClassName, LocalSelectorConfig.OpenClassName);
        resize(e.target, LocalSelectorConfig.CloseClassName, LocalSelectorConfig.OpenClassName);
    };
    this.changeCheck = function(e)
    {
        if(e.target.checked)
        {
            LocalSelectorConfig.addToSelected(
                e.target.parentNode.parentNode.parentNode.parentNode.id,
                e.target.value
            );
        }
        else
        {
            LocalSelectorConfig.removeFromSelected(
                e.target.parentNode.parentNode.parentNode.parentNode.id,
                e.target.value
            );
        }
    };

    this.generateUlNew = function(Nodes)
    {
        var TreeUl;
        var LiNode;
        var SpanNode;
        var ButtonDiv;
        var UlNode;
        var FeatureText;
        
        var CheckFlag;

        LocalSelectorConfig.AllFeatures = Nodes[0];
        if(!LocalSelectorConfig.AllFeatures.length)
        {
            document.getElementById(LocalSelectorConfig.DivNameId).innerHTML = "";
            document.getElementById(LocalSelectorConfig.DivNameId).style.display = "none";
            return null;
        }
        LocalSelectorConfig.Selected = Nodes[1];
        
        TreeUl = document.createElement('ul');
        for( var i=0; i < LocalSelectorConfig.AllFeatures.length; i++ )
        {
            LiNode = document.createElement('li');
            LiNode.id = LocalSelectorConfig.AllFeatures[i].ID_Feature;
            ButtonDiv = document.createElement('div');
            ButtonDiv.classList.toggle(LocalSelectorConfig.CloseClassName);
            ButtonDiv.onclick = this.clickButton; //resize(this, "OpenButton", "CloseButton");
            SpanNode = document.createElement('span');
            FeatureText = LocalSelectorConfig.AllFeatures[i].FeatureTitle;
            if(LocalSelectorConfig.AllFeatures[i].Reserv.length)
            {
                FeatureText += ", " + LocalSelectorConfig.AllFeatures[i].Reserv;
            }
            SpanNode.innerHTML = FeatureText;
            UlNode = document.createElement('ul');
            UlNode.style.height = 0;
            UlNode.style.overflow = "hidden";
            
            CheckFlag = false;
            
            for( var k=0; k < LocalSelectorConfig.AllFeatures[i].Values.length; k++ )
            {
                var NewLiNode;
                var LabelNode;
                var CheckNode;
                var TextNode;
                
                NewLiNode = document.createElement('li');
                UlNode.appendChild(NewLiNode);

                LabelNode = document.createElement('label');
                NewLiNode.appendChild(LabelNode);
                
                CheckNode = document.createElement('input');
                LabelNode.appendChild(CheckNode);

                TextNode = document.createTextNode(LocalSelectorConfig.AllFeatures[i].Values[k].FeaturesValue);
                LabelNode.appendChild(TextNode);
                
                CheckNode.type = "checkbox";
                CheckNode.name = "featurescheck[]";
                CheckNode.value = LocalSelectorConfig.AllFeatures[i].Values[k].FeaturesValue;
                CheckNode.onchange = this.changeCheck;

                for( var cnt = 0; cnt < LocalSelectorConfig.Selected.length; cnt++ )
                {
                    if( LocalSelectorConfig.AllFeatures[i].Values[k].ID_Feature === LocalSelectorConfig.Selected[cnt].id
                        && 
                        LocalSelectorConfig.AllFeatures[i].Values[k].FeaturesValue === LocalSelectorConfig.Selected[cnt].value
                        )
                    {
                        CheckNode.checked = true;
                        CheckFlag = true;
                    }
                }
            }

            if( CheckFlag )
            {
                UlNode.style.height = "auto";
                //switchClass(ButtonDiv, LocalSelectorConfig.OpenClassName, LocalSelectorConfig.CloseClassName);
                ButtonDiv.classList.toggle(LocalSelectorConfig.CloseClassName);
                ButtonDiv.classList.toggle(LocalSelectorConfig.OpenClassName);
            }

            LiNode.appendChild(ButtonDiv);
            LiNode.appendChild(SpanNode);
            LiNode.appendChild(UlNode);
            
            TreeUl.appendChild(LiNode);
        }
        return TreeUl;
    };

    this.sendSelectorRequest = function()
    {
        if(!LocalSelectorConfig.Selected.length)
        {
            alert("Нет выбранных характеристик!");
            return;
        }
        var CurrentUrl = "";
        var CurrentName = "";
        var FirstFlag = true;
        var OldName = "";

        for (var i = 0; i < LocalSelectorConfig.Selected.length; i++)
        {
            CurrentName = LocalSelectorConfig.getTranslitForId(LocalSelectorConfig.Selected[i].id);
            if( FirstFlag || CurrentName !== OldName )
            {
                CurrentUrl += "/" + CurrentName+"-eqv-"+encodeURIComponent(LocalSelectorConfig.Selected[i].value);
                FirstFlag = false;
            }
            else
            {
                CurrentUrl += "-or-" +encodeURIComponent(LocalSelectorConfig.Selected[i].value);
            }
            OldName = CurrentName;
        }
        
        var URLArr = document.location.pathname.match(LocalSelectorConfig.PricePrefix + "/([^/]+)/?");

        if(!URLArr[1])
        {
            throw "Не правильный адрес страницы!";
        }
        CurrentUrl = "/" + LocalSelectorConfig.PricePrefix + "/" + URLArr[1] + CurrentUrl;

//        window.history.pushState({}, "", CurrentUrl);
        window.location = CurrentUrl;
    };

}
