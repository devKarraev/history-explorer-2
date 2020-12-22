function textwrap(text, width, m= 5) {

    //console.log(text);
    var x0 =5;
    text.each(function(d) {

        let w = width;
        if(width < 0) {
            //w = elementWidth * Math.max(d.nVIn, d.nVOut, minNodeWUnits) - 2 * m;
           w =  nodeWidth(d) - 2 * m;
        }
        var text = d3.select(this),
            words = text.text().split(/\s+/).reverse(),
            word,
            line = [],
            lineNumber = 0,
            lineHeight = 1.1, // ems
            y = text.attr("y"),
            dy = parseFloat(text.attr("dy")),
            tspan = text.text(null).append("tspan").attr("x", m).attr("y", y).attr("dy", dy + "em");
        while (word = words.pop()) {
            line.push(word);
            tspan.text(line.join(" "));
            if (tspan.node().getComputedTextLength() > w) {
                line.pop();
                tspan.text(line.join(" "));
                line = [word];
                tspan = text.append("tspan").attr("x", m).attr("y", y).attr("dy", ++lineNumber * lineHeight + dy + "em").text(word);
            }
        }
    });
}
