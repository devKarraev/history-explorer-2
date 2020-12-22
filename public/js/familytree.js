$(document).ready(function() {
    $("#search_name").on('keyup', function () {

        var name = ".elements[name='" +  $(this).val() + "']";
        $(".elements.selected").removeClass("selected");
        $(name).addClass("selected");
    });
})

    function example() {

        var svg;
        var margin = {
            top: 60,
            bottom: 80,
            left: 60,
            right: 0
        };
        var width = 500;
        var height = 400;

        var zoomable = true;

        var zoom = d3.zoom()
            .scaleExtent([1, 40])
            .translateExtent([[-100, -100], [width + 90, height + 100]])
            .on("zoom", zoomable ? zoomed : null);

        var zoomX = d3.zoom()
            .scaleExtent([1, 40])
            .translateExtent([[-100, -100], [width + 90, height + 100]])
            .on("zoom", zoomable ? zoomedX : null);

        var xscale = d3.scaleLinear()
            .domain([-1, width + 1])
            .range([-1, width + 1]);

        var yscale = d3.scaleLinear()
            .domain([-1, height + 1])
            .range([-1, height + 1]);

        var xAxis = d3.axisBottom(xscale)
            .ticks((width + 2) / (height + 2) * 10)
            .tickSize(height)
            .tickPadding(8 - height);

        var yAxis = d3.axisRight(yscale)
            .ticks(10)
            .tickSize(width)
            .tickPadding(8 - width);

        var view = svg.append("rect")
            .attr("class", "view")
            .attr("x", 0.5)
            .attr("y", 0.5)
            .attr("width", width - 1)
            .attr("height", height - 1);

        var gX = svg.append("g")
            .attr("class", "axis axis--x")
            .call(xAxis);

        var gY = svg.append("g")
            .attr("class", "axis axis--y")
            .call(yAxis);

        svg.call(zoom);

        function zoomed() {
            view.attr("transform", d3.event.transform);
            gX.call(xAxis.scale(d3.event.transform.rescaleX(x)));
            gY.call(yAxis.scale(d3.event.transform.rescaleY(y)));
        }

        function zoomedX() {
            view.attr("transform", d3.event.transform);
            gX.call(xAxis.scale(d3.event.transform.rescaleX(x)));
        }


        function chart(selection) {
            selection.each(function(data) {
                svg = d3.select(this).selectAll('svg').data([data]);
                svg.enter().append('svg');
                var g = svg.append('g')
                    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

                g.append("defs").append("clipPath")
                    .attr("id", "clip")
                    .append("rect")
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom);

                g.append("svg:rect")
                    .attr("class", "border")
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom)
                    .style("stroke", "black")
                    .style("fill", "none");

                g.append("g").attr("class", "x axis")
                    .attr("transform", "translate(" + 0 + "," + (height - margin.top - margin.bottom) + ")");

                g.append("g").attr("class", "y axis");

                g.append("g")
                    .attr("class", "scatter")
                    .attr("clip-path", "url(#clip)");

                g
                    .append("svg:rect")
                    .attr("class", "zoom xy box")
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom)
                    .style("visibility", "hidden")
                    .attr("pointer-events", "all")
                    .call(xyzoom);

                g
                    .append("svg:rect")
                    .attr("class", "zoom x box")
                    .attr("width", width - margin.left - margin.right)
                    .attr("height", height - margin.top - margin.bottom)
                    .attr("transform", "translate(" + 0 + "," + (height - margin.top - margin.bottom) + ")")
                    .style("visibility", "hidden")
                    .attr("pointer-events", "all")
                    .call(xzoom);

                g
                    .append("svg:rect")
                    .attr("class", "zoom y box")
                    .attr("width", margin.left)
                    .attr("height", height - margin.top - margin.bottom)
                    .attr("transform", "translate(" + -margin.left + "," + 0 + ")")
                    .style("visibility", "hidden")
                    .attr("pointer-events", "all")
                    .call(yzoom);

                // Update the x-axis
                xscale.domain(d3.extent(data, function(d) {
                    return d[0];
                }))
                    .range([0, width - margin.left - margin.right]);

                xaxis.scale(xscale)
                    .orient('bottom')
                    .tickPadding(10);

                svg.select('g.x.axis').call(xaxis);

                // Update the y-scale.
                yscale.domain(d3.extent(data, function(d) {
                    return d[1];
                }))
                    .range([height - margin.top - margin.bottom, 0]);

                yaxis.scale(yscale)
                    .orient('left')
                    .tickPadding(10);

                svg.select('g.y.axis').call(yaxis);

                draw();
            });

            return chart;
        }

        function update() {
            var gs = svg.select("g.scatter");

            var circle = gs.selectAll("circle")
                .data(function(d) {
                    return d;
                });

            circle.enter().append("svg:circle")
                .attr("class", "points")
                .style("fill", "steelblue")
                .attr("cx", function(d) {
                    return X(d);
                })
                .attr("cy", function(d) {
                    return Y(d);
                })
                .attr("r", 4);

            circle.attr("cx", function(d) {
                return X(d);
            })
                .attr("cy", function(d) {
                    return Y(d);
                });

            circle.exit().remove();
        }

        function zoom_update() {
            xyzoom = d3.behavior.zoom()
                .x(xscale)
                .y(yscale)
                .on("zoom", zoomable ? draw : null);
            xzoom = d3.behavior.zoom()
                .x(xscale)
                .on("zoom", zoomable ? draw : null);
            yzoom = d3.behavior.zoom()
                .y(yscale)
                .on("zoom", zoomable ? draw : null);

            svg.select('rect.zoom.xy.box').call(xyzoom);
            svg.select('rect.zoom.x.box').call(xzoom);
            svg.select('rect.zoom.y.box').call(yzoom);
        }

        function draw() {
            svg.select('g.x.axis').call(xaxis);
            svg.select('g.y.axis').call(yaxis);

            update();
            zoom_update();
        };

        // X value to scale

        function X(d) {
            return xscale(d[0]);
        }

        // Y value to scale

        function Y(d) {
            return yscale(d[1]);
        }

        chart.zoom = function (_){
            if (!arguments.length) return zoomable;
            zoomable = _;
            return chart;
        }

        return chart;

};