var nodes = [
    {"id": "Alice"},
    {"id": "Bob"},
    {"id": "Carol"}
];


var links = [
    {"source": "Alice", "target": "Bob"},
    {"source": "Bob", "target": "Carol"}
];

function id(d) {
    return d.id;
}

svg.append("g")
    .attr("fill", "none")
    .attr("stroke", "#000")
    .attr("stroke-opacity", 0.2)
    .selectAll("path")
    .data(graph.links)
    .join("path")
    .attr("d", d3.sankeyLinkHorizontal())
    .attr("stroke-width", function(d) { return d.width; });
