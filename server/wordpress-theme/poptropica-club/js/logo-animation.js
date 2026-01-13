/**
 * Logo Mouse Avoidance Animation
 * Makes logo letters push away from cursor and float when idle
 */
(function() {
    const logoContainer = document.getElementById('logo-container');
    if (!logoContainer) return;

    // Load SVG and set up mouse avoidance
    const xhr = new XMLHttpRequest();
    xhr.open('GET', logoContainer.dataset.svgUrl, true);
    xhr.onload = function() {
        if (xhr.status === 200 || xhr.status === 0) {
            logoContainer.innerHTML = xhr.responseText;

            const logoParts = logoContainer.querySelectorAll('[data-animate="logo-part"]');
            const logoAvoidRadius = 300;
            const logoMaxPush = 120;

            const logoStates = Array.from(logoParts).map(() => ({ x: 0, y: 0, rotate: 0 }));

            // Assign random float animations to each part
            logoParts.forEach((part, i) => {
                const animIndex = (i % 8) + 1;
                const delay = (i * 0.15) % 2;
                part.style.animation = `logoFloat${animIndex} ${1.7 + (i % 8) * 0.1}s ease-in-out infinite`;
                part.style.animationDelay = `${delay}s`;
            });

            function updateLogoParts() {
                logoParts.forEach((part, i) => {
                    const state = logoStates[i];
                    part.style.setProperty('--logo-push-x', state.x + 'px');
                    part.style.setProperty('--logo-push-y', state.y + 'px');
                    part.style.setProperty('--logo-push-rotate', state.rotate + 'deg');
                });
                requestAnimationFrame(updateLogoParts);
            }
            updateLogoParts();

            // Messy Sinker's letters - animate each letter with all 3 stroke layers together
            const messyGroup = logoContainer.querySelector('#messySinkers');
            const messyLayers = messyGroup ? messyGroup.querySelectorAll(':scope > g') : [];
            const messyLetters = [];

            if (messyLayers.length === 3) {
                const numLetters = messyLayers[0].querySelectorAll('path').length;
                for (let i = 0; i < numLetters; i++) {
                    const paths = Array.from(messyLayers).map(layer =>
                        layer.querySelectorAll('path')[i]
                    );
                    messyLetters.push({
                        paths: paths,
                        state: { x: 0, y: 0, rotate: 0 }
                    });
                    // Assign subtle float animation to each letter
                    const animIndex = (i % 8) + 1;
                    const delay = (i * 0.2) % 2;
                    paths.forEach(path => {
                        path.style.transformOrigin = 'center center';
                        path.style.animation = `messyFloat${animIndex} ${2.5 + (i % 8) * 0.15}s ease-in-out infinite`;
                        path.style.animationDelay = `${delay}s`;
                    });
                }
            }

            function updateMessyLetters() {
                messyLetters.forEach(letter => {
                    const { paths, state } = letter;
                    paths.forEach(path => {
                        path.style.setProperty('--logo-push-x', state.x + 'px');
                        path.style.setProperty('--logo-push-y', state.y + 'px');
                        path.style.setProperty('--logo-push-rotate', state.rotate + 'deg');
                    });
                });
                requestAnimationFrame(updateMessyLetters);
            }
            updateMessyLetters();

            document.addEventListener('mousemove', (e) => {
                // Logo parts (Poptropica Club letters)
                logoParts.forEach((part, i) => {
                    const rect = part.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;

                    const dx = centerX - e.clientX;
                    const dy = centerY - e.clientY;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    let targetX = 0, targetY = 0, targetRotate = 0;
                    if (distance < logoAvoidRadius) {
                        const force = Math.pow((logoAvoidRadius - distance) / logoAvoidRadius, 0.7);
                        const angle = Math.atan2(dy, dx);
                        targetX = Math.cos(angle) * logoMaxPush * force;
                        targetY = Math.sin(angle) * logoMaxPush * force;
                        targetRotate = targetX * 0.3;
                    }

                    const ease = 0.15;
                    logoStates[i].x += (targetX - logoStates[i].x) * ease;
                    logoStates[i].y += (targetY - logoStates[i].y) * ease;
                    logoStates[i].rotate += (targetRotate - logoStates[i].rotate) * ease;
                });

                // Messy Sinker's letters
                messyLetters.forEach(letter => {
                    const rect = letter.paths[0].getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;

                    const dx = centerX - e.clientX;
                    const dy = centerY - e.clientY;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    let targetX = 0, targetY = 0, targetRotate = 0;
                    if (distance < logoAvoidRadius) {
                        const force = Math.pow((logoAvoidRadius - distance) / logoAvoidRadius, 0.7);
                        const angle = Math.atan2(dy, dx);
                        targetX = Math.cos(angle) * logoMaxPush * force;
                        targetY = Math.sin(angle) * logoMaxPush * force;
                        targetRotate = targetX * 0.3;
                    }

                    const ease = 0.15;
                    letter.state.x += (targetX - letter.state.x) * ease;
                    letter.state.y += (targetY - letter.state.y) * ease;
                    letter.state.rotate += (targetRotate - letter.state.rotate) * ease;
                });
            });
        }
    };
    xhr.send();
})();
