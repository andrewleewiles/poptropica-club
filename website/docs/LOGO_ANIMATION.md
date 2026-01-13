# Logo Mouse Avoidance Animation

Interactive animation system for the Poptropica Club logo that makes letters push away from the mouse cursor and gently float when idle.

## Overview

The logo consists of two animated sections:
- **Poptropica Club letters** - Main logo text with prominent float animation
- **Messy Sinker's text** - Subtitle with subtle float animation

Both sections respond to mouse proximity by pushing away from the cursor.

## File Structure

```
website/
├── shared/
│   └── pcLogo2.svg          # SVG with animation-ready structure
└── landing/
    ├── index.html           # JavaScript animation logic
    └── styles.css           # CSS keyframes and transform properties
```

## SVG Structure (pcLogo2.svg)

### Required Attributes

The SVG must have an expanded viewBox to prevent clipping during animation:

```xml
<svg width="2325.6" height="1517.1" viewBox="-300 -300 2325.6 1517.1">
```

### Poptropica Club Letters

Each letter group needs the `data-animate="logo-part"` attribute:

```xml
<g id="logo">
    <g data-animate="logo-part"><!-- P --></g>
    <g data-animate="logo-part"><!-- O --></g>
    <g data-animate="logo-part"><!-- P --></g>
    <!-- ... 15 letter groups total -->
</g>
```

### Messy Sinker's Text

The Messy Sinker's section uses a different structure with 3 stroke layers:

```xml
<g id="messySinkers">
    <g><!-- Layer 1: Dark stroke (st11) - 14 letter paths --></g>
    <g><!-- Layer 2: Light stroke (st12) - 14 letter paths --></g>
    <g><!-- Layer 3: White fill (st21) - 14 letter paths --></g>
</g>
```

Each layer contains the same 14 letter paths in the same order. The JavaScript groups corresponding paths across layers so each letter (with all 3 strokes) animates as a unit.

## CSS (styles.css)

### Transform Properties

Both animated element types use CSS custom properties for transforms:

```css
[data-animate="logo-part"],
#messySinkers path {
    transform-origin: center center;
    will-change: transform;
    --logo-push-x: 0px;
    --logo-push-y: 0px;
    --logo-push-rotate: 0deg;
}
```

### Float Animations

**Main logo letters** use `logoFloat1-8` with more pronounced movement:
- translateY: 3-6px
- rotate: 1.5-5deg
- Duration: ~1.7-2.4s

**Messy Sinker's letters** use `messyFloat1-8` with subtle movement:
- translateY: 1-2px
- rotate: 0.4-0.7deg
- Duration: ~2.5-3.7s

Each keyframe combines the idle float with mouse-push transforms:

```css
@keyframes logoFloat1 {
    0%, 100% {
        transform: translate(var(--logo-push-x), var(--logo-push-y))
                   rotate(var(--logo-push-rotate))
                   translateY(0) rotate(0deg);
    }
    50% {
        transform: translate(var(--logo-push-x), var(--logo-push-y))
                   rotate(var(--logo-push-rotate))
                   translateY(-4px) rotate(2deg);
    }
}
```

## JavaScript (index.html)

### SVG Loading

The SVG is loaded via XMLHttpRequest and injected into the DOM (required for accessing internal elements):

```javascript
const xhr = new XMLHttpRequest();
xhr.open('GET', '../shared/pcLogo2.svg', true);
xhr.onload = function() {
    logoContainer.innerHTML = xhr.responseText;
    // Setup animations...
};
xhr.send();
```

### Configuration

```javascript
const logoAvoidRadius = 300;  // Mouse detection radius (px)
const logoMaxPush = 120;      // Maximum push distance (px)
```

### Animation Loop

Each frame updates the CSS custom properties based on interpolated state:

```javascript
function updateLogoParts() {
    logoParts.forEach((part, i) => {
        const state = logoStates[i];
        part.style.setProperty('--logo-push-x', state.x + 'px');
        part.style.setProperty('--logo-push-y', state.y + 'px');
        part.style.setProperty('--logo-push-rotate', state.rotate + 'deg');
    });
    requestAnimationFrame(updateLogoParts);
}
```

### Mouse Interaction

On mouse move, calculates push direction and force for each element:

```javascript
document.addEventListener('mousemove', (e) => {
    logoParts.forEach((part, i) => {
        const rect = part.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        const dx = centerX - e.clientX;
        const dy = centerY - e.clientY;
        const distance = Math.sqrt(dx * dx + dy * dy);

        if (distance < logoAvoidRadius) {
            const force = Math.pow((logoAvoidRadius - distance) / logoAvoidRadius, 0.7);
            const angle = Math.atan2(dy, dx);
            targetX = Math.cos(angle) * logoMaxPush * force;
            targetY = Math.sin(angle) * logoMaxPush * force;
            targetRotate = targetX * 0.3;
        }

        // Smooth interpolation
        const ease = 0.15;
        logoStates[i].x += (targetX - logoStates[i].x) * ease;
        logoStates[i].y += (targetY - logoStates[i].y) * ease;
        logoStates[i].rotate += (targetRotate - logoStates[i].rotate) * ease;
    });
});
```

### Messy Sinker's Letter Grouping

Letters are grouped by index across all 3 stroke layers:

```javascript
const messyGroup = logoContainer.querySelector('#messySinkers');
const messyLayers = messyGroup.querySelectorAll(':scope > g');

for (let i = 0; i < numLetters; i++) {
    const paths = Array.from(messyLayers).map(layer =>
        layer.querySelectorAll('path')[i]
    );
    messyLetters.push({
        paths: paths,  // All 3 stroke versions of this letter
        state: { x: 0, y: 0, rotate: 0 }
    });
}
```

## Tuning Parameters

| Parameter | Location | Default | Description |
|-----------|----------|---------|-------------|
| `logoAvoidRadius` | index.html | 300 | Mouse detection radius in pixels |
| `logoMaxPush` | index.html | 120 | Maximum push distance in pixels |
| `ease` | index.html | 0.15 | Interpolation speed (0-1, lower = smoother) |
| `rotateMultiplier` | index.html | 0.3 | Rotation intensity relative to X push |
| viewBox padding | pcLogo2.svg | 300 | Extra space around logo for animation overflow |

### Float Animation Intensity

To adjust idle float intensity, modify the keyframe values in styles.css:

**More intense float:**
```css
50% { transform: ... translateY(-8px) rotate(4deg); }
```

**Subtler float:**
```css
50% { transform: ... translateY(-2px) rotate(1deg); }
```

## Adding New Animated Elements

1. Add `data-animate="logo-part"` to the SVG group
2. The JavaScript will automatically detect and animate it
3. Ensure the SVG viewBox has enough padding for the animation

## Browser Support

- Requires CSS custom properties (CSS variables)
- Requires `requestAnimationFrame`
- Uses `:scope` selector for Messy Sinker's (IE not supported)
