document.addEventListener("DOMContentLoaded", () => {
    const head = document.getElementById("head");
    const eyes = document.querySelectorAll(".eye");
    const pupils = document.querySelectorAll(".pupil");

    const allParts = ["head", "body", "lArm", "rArm", "lLeg", "rLeg"].map(id =>
        document.getElementById(id)
    );

    const childSafeParts = ["body", "lArm", "rArm", "lLeg", "rLeg"].map(id =>
        document.getElementById(id)
    );

    document.getElementById("safety").addEventListener("change", (e) => {
        const rounded = e.target.value === "rounded";
        childSafeParts.forEach(part => {
            part.classList.toggle("rounded", rounded);
        });
    });

    document.getElementById("headStyle").addEventListener("change", (e) => {
        if (e.target.value === "round") {
            head.classList.add("half-circle");
        } else {
            head.classList.remove("half-circle");
        }
    });

    document.getElementById("alignment").addEventListener("change", (e) => {
        const color = e.target.value === "evil" ? "red" : "black";
        pupils.forEach(p => p.style.backgroundColor = color);
    });

    document.getElementById("eyes").addEventListener("change", (e) => {
        const round = e.target.value === "round";
        eyes.forEach(eye => {
            eye.classList.toggle("round", round);
        });
    });

    document.getElementById("eyeColor").addEventListener("input", (e) => {
        eyes.forEach(eye => eye.style.backgroundColor = e.target.value);
    });


    document.getElementById("robotColor").addEventListener("input", (e) => {
        const color = e.target.value;
        allParts.forEach(part => part.style.backgroundColor = color);
    });

    document.getElementById("reset-btn").addEventListener("click", function () {
        document.getElementById("safety").value = "sharp";
        document.getElementById("headStyle").value = "square";
        document.getElementById("alignment").value = "good";
        document.getElementById("eyes").value = "square";
        document.getElementById("eyeColor").value = "#ffffff";
        document.getElementById("robotColor").value = "#c0c0c0";

        document.getElementById("safety").dispatchEvent(new Event("change"));
        document.getElementById("headStyle").dispatchEvent(new Event("change"));
        document.getElementById("alignment").dispatchEvent(new Event("change"));
        document.getElementById("eyes").dispatchEvent(new Event("change"));
        document.getElementById("eyeColor").dispatchEvent(new Event("input"));
        document.getElementById("robotColor").dispatchEvent(new Event("input"));
    });

    document.getElementById("shuffle-btn").addEventListener("click", function () {
        const safetyArray = ["sharp", "rounded"];
        const headArray = ["square", "round"];
        const alignmentArray = ["good", "evil"];
        const eyeArray = ["square", "round"];

        const safety = randomize(safetyArray);
        const headStyle = randomize(headArray);
        const alignment = randomize(alignmentArray);
        const eye = randomize(eyeArray);
        const eyeColor = randomColor();
        const robotColor = randomColor();

        document.getElementById("safety").value = safety;
        document.getElementById("headStyle").value = headStyle;
        document.getElementById("alignment").value = alignment;
        document.getElementById("eyes").value = eye;
        document.getElementById("eyeColor").value = eyeColor;
        document.getElementById("robotColor").value = robotColor;

        document.getElementById("safety").dispatchEvent(new Event("change"));
        document.getElementById("headStyle").dispatchEvent(new Event("change"));
        document.getElementById("alignment").dispatchEvent(new Event("change"));
        document.getElementById("eyes").dispatchEvent(new Event("change"));
        document.getElementById("eyeColor").dispatchEvent(new Event("input"));
        document.getElementById("robotColor").dispatchEvent(new Event("input"));
    });

    function randomize(array) {
        return array[Math.floor(Math.random() * array.length)];
    }

    function randomColor() {
        var letters = "0123456789ABCDEF";
        var color = "#";
        for (var i = 0; i < 6; i++) {
            var randomIndex = Math.floor(Math.random() * 16);
            color = color + letters[randomIndex];
        }
        return color;
    }

});
