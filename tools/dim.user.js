// ==UserScript==
// @name           OGame Redesign: Dimed Returning Fleets
// @namespace      qdscripter
// @include        http://*.ogame.*/game/index.php*
// ==/UserScript==

(function()
{
	var eventboxContent = document.getElementById("eventboxContent");
	if (!eventboxContent) return;

	var ArrayContains = function(array, element)
	{
		if (!array) return null;
		for (i = 0; i < array.length; i++)
		{
			if (array[i] == element) return element;
		}
		return null;
	}

	var IsElement = function(element, tag, id, class)
	{
		return element && (!tag || element.tagName == tag) && (!id || element.id == id) &&
			(!class || ArrayContains(element.getAttribute("class").split(" "), class));
	}

	var SameText = function(s1, s2)
	{
		return (s1 == null) ? (s2 == null) : ((s2 == null) ? false : (s1.toUpperCase() == s2.toUpperCase()));
	}

	var SelectChildNode = function(parent, nodeTag, attrName, attrValue, nodeNum)
	{
		if (parent == null) return null;
		var child = parent.firstChild;
		while (child != null)
		{
			var node = child;
			child = child.nextSibling;
			if (nodeTag != null && !SameText(node.tagName, nodeTag)) continue;
			if (attrName != null && !SameText(node.getAttribute(attrName), attrValue)) continue;
			if (nodeNum != null && --nodeNum > 0) continue;
			return node;
		}
		return null;
	}

	var EventList =
	{
		EventListDOMNodeInserted: function(e)
		{
			if(!e || !e.target || !e.target.id) return;
			if( e.target.id == "eventListWrap") EventList.CatchEventList(e.target);
		},

		CatchEventList: function (eventListWrap)
		{
			try 
			{
				var eventContent = SelectChildNode(eventListWrap, "TABLE", "id", "eventContent");
				if (!eventContent) return;
				var tbody = SelectChildNode(eventContent, "TBODY", null, null);
				if (!tbody) return;
				for (var i = 0; i < tbody.childNodes.length; i++)
				{
					var tr = tbody.childNodes[i];
					if (!IsElement(tr, "TR", null, "eventFleet")) continue;
					if (!SelectChildNode(tr, "TD", "class", "icon_movement_reserve")) continue;
					var td = tr.firstChild;
					while (td)
					{
						if (IsElement(td, "TD", null, null))
						{
							td.style.opacity = 0.3;
						}
						td = td.nextSibling;
					}
				}
			}
			catch(error)
			{
				alert("Unexpected error: " + error);
			}
		},

		Run: function()
		{
			eventListWrap = document.getElementById("eventListWrap");
			if (eventListWrap)
			{
				EventList.CatchEventList(eventListWrap);
			}
			eventboxContent.addEventListener("DOMNodeInserted", EventList.EventListDOMNodeInserted, false);
		}
	};

	EventList.Run();
}
)();