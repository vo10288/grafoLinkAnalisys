<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Grafo Geografico</title>
    <script src="https://d3js.org/d3.v6.min.js"></script>
    <style>
        svg {
            background-color: #F5F5F5;
        }

        .link {
            stroke: #999;
            stroke-opacity: 0.6;
        }

        .node circle {
            fill: #fff;
            stroke: #666;
            stroke-width: 2px;
        }

        .node text {
            font-size: 12px;
        }
    </style>
</head>
<body>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="submit" value="Carica">
    </form>

    <svg width="800" height="600"></svg>

    <script>
        function handleFile(file) {
            var reader = new FileReader();

            reader.onload = function (event) {
                var contents = event.target.result;
                var lines = contents.split("\n");

                var dati = [];
                lines.forEach(function (line) {
                    var fields = line.split(";");
                    dati.push(fields);
                });

                var grafo = { nodes: [], links: [] };

                dati.forEach(function (riga) {
                    var societa = riga[0];
                    var indirizzoSocieta = riga[1];
                    var amministratore = riga[2];
                    var indirizzoAmministratore = riga[3];

                    if (!grafo.nodes.find(function (n) { return n.id === societa; })) {
                        grafo.nodes.push({ id: societa, address: indirizzoSocieta });
                    }
                    if (!grafo.nodes.find(function (n) { return n.id === amministratore; })) {
                        grafo.nodes.push({ id: amministratore, address: indirizzoAmministratore });
                    }

                    grafo.links.push({ source: societa, target: amministratore });
                });

                var svg = d3.select("svg");
                var width = +svg.attr("width");
                var height = +svg.attr("height");

                var simulation = d3.forceSimulation()
                    .force("link", d3.forceLink().id(function (d) { return d.id; }))
                    .force("charge", d3.forceManyBody())
                    .force("center", d3.forceCenter(width / 2, height / 2));

                var link = svg.append("g")
                    .attr("class", "links")
                    .selectAll("line")
                    .data(grafo.links)
                    .enter().append("line")
                    .attr("class", "link");

                var node = svg.append("g")
                    .attr("class", "nodes")
                    .selectAll("g")
                    .data(grafo.nodes)
                    .enter().append("g");

                var circles = node.append("circle")
                    .attr("r", 10)
                    .attr("fill", function (d) {
                        return (d.id.startsWith("Società")) ? "blue" : "green
