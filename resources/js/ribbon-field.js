/**
 * Ribbon Field background — vanilla JS port of ThreeUI's RibbonFieldBackground
 * (variant `ribbon-field`, source revision SHA-256 fa86582fc870).
 *
 * The original is React + raw WebGL (no Three.js runtime). This port keeps the
 * exact shaders, uniforms, pointer maths, observers, and cleanup, adapted to
 * plain DOM so the Blade login page can mount it without a React runtime.
 *
 * Usage: <div class="shader-frame"><div data-ribbon-field></div></div>
 * then mountRibbonField(document.querySelector('[data-ribbon-field]')).
 */

export const RIBBON_FIELD_DEFAULTS = {
    speed: 1,
    pointerAmount: 1,
    smoothing: 0.035,
    brightness: 1,
    opacity: 1,
    hue: 0,
    saturation: 1,
};

const RIBBON_FIELD_VERTEX_SHADER = `
        attribute vec2 position;
        void main() {
          gl_Position = vec4(position, 0.0, 1.0);
        }
      `;

const RIBBON_FIELD_FRAGMENT_SHADER = `
        precision highp float;
        uniform vec2 resolution;
        uniform float time;
        uniform vec2 pointer;

        float hash(vec2 p) {
          p = fract(p * vec2(123.34, 456.21));
          p += dot(p, p + 45.32);
          return fract(p.x * p.y);
        }

        float ribbon(vec2 uv, float offset, float width, float phase) {
          float y = 0.55 + 0.20 * sin((uv.x * 2.15) + phase) + 0.045 * sin((uv.x * 7.0) - phase * 0.7);
          float d = abs(uv.y - y - offset);
          return exp(-(d * d) / width);
        }

        void main() {
          vec2 uv = gl_FragCoord.xy / resolution.xy;
          vec2 p = uv;
          p.x *= resolution.x / resolution.y;

          float t = time * 0.22;
          float drift = (pointer.x - 0.5) * 0.06;

          // Adapted from the ThreeUI source (smoothstep(0.28, 0.72, uv.x)):
          // the login brand panel occupies the left half of the viewport, so
          // the field spans the full width instead of fading out there.
          float rightFade = smoothstep(0.0, 0.15, uv.x);
          float centerDark = 1.0 - smoothstep(0.0, 0.88, distance(uv, vec2(0.18, 0.48)));

          float r1 = ribbon(vec2(uv.x + drift, uv.y), 0.03, 0.0065, t + 0.9);
          float r2 = ribbon(vec2(uv.x - drift * 0.7, uv.y), -0.23, 0.0085, t + 3.25);
          float r3 = ribbon(vec2(uv.x + drift * 0.4, uv.y), 0.25, 0.014, t + 1.85);

          float glow = r1 * 1.14 + r2 * 1.05 + r3 * 0.48;

          vec3 teal = vec3(0.17, 0.83, 0.75);
          vec3 cyan = vec3(0.22, 0.82, 0.96);
          vec3 indigo = vec3(0.39, 0.38, 0.92);
          vec3 purple = vec3(0.66, 0.33, 0.98);
          vec3 blue = vec3(0.23, 0.51, 0.96);

          vec3 col = vec3(0.0);
          col += cyan * r1 * 0.92;
          col += teal * r1 * 0.62;
          col += indigo * r3 * 0.42;
          col += blue * r2 * 0.66;
          col += purple * (r2 + r3) * 0.30;

          float bloom = exp(-pow(distance(uv, vec2(0.76, 0.40 + 0.035 * sin(t))), 2.0) / 0.050);
          bloom += exp(-pow(distance(uv, vec2(0.71, 0.75 + 0.025 * cos(t))), 2.0) / 0.030);
          col += vec3(0.42, 0.85, 1.0) * bloom * 0.34;

          vec2 grid = fract(gl_FragCoord.xy / 7.0) - 0.5;
          float dotShape = smoothstep(0.29, 0.11, length(grid));
          float noise = hash(floor(gl_FragCoord.xy / 7.0));
          float scan = 0.72 + 0.28 * sin((uv.x + uv.y) * 38.0 + time * 1.3);
          float dots = dotShape * (0.48 + 0.52 * noise) * scan;

          float micro = hash(gl_FragCoord.xy + time) * 0.035;
          float alpha = clamp((glow * 1.55 + bloom * 0.50) * dots * rightFade, 0.0, 1.0);
          alpha *= 1.0 - centerDark * 0.56;

          vec3 base = vec3(0.005, 0.005, 0.005);
          vec3 finalColor = mix(base, col, clamp(alpha * 1.55, 0.0, 1.0));
          finalColor += micro * rightFade;

          gl_FragColor = vec4(finalColor, 1.0);
        }
      `;

/** Compile a single shader, mirroring the original `compile()` helper. */
const compile = (gl, type, source) => {
    const shader = gl.createShader(type);

    if (!shader) {
        throw new Error('Unable to create Ribbon Field shader');
    }

    gl.shaderSource(shader, source);
    gl.compileShader(shader);

    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
        throw new Error(gl.getShaderInfoLog(shader) || 'Ribbon Field shader compilation failed');
    }

    return shader;
};

/**
 * Mount the ribbon field inside `host` (a plain element).
 *
 * Returns a cleanup function that tears the WebGL context resources down,
 * exactly like the original component's effect cleanup.
 *
 * @param {HTMLElement} host Element that becomes `.threeui-background.ribbon-field`.
 * @param {Partial<typeof RIBBON_FIELD_DEFAULTS>} props Configured usage props.
 * @returns {(() => void) | null}
 */
export function mountRibbonField(host, props = {}) {
    if (!host) {
        return null;
    }

    const options = { ...RIBBON_FIELD_DEFAULTS, ...props };

    host.classList.add('threeui-background', 'ribbon-field');

    const canvas = document.createElement('canvas');
    canvas.style.opacity = String(options.opacity);
    canvas.style.filter = `hue-rotate(${options.hue}deg) saturate(${options.saturation}) brightness(${options.brightness})`;
    host.appendChild(canvas);

    const gl = canvas.getContext('webgl', {
        alpha: true,
        antialias: false,
        premultipliedAlpha: false,
    });

    if (!gl) {
        return null;
    }

    const vertex = compile(gl, gl.VERTEX_SHADER, RIBBON_FIELD_VERTEX_SHADER);
    const fragment = compile(gl, gl.FRAGMENT_SHADER, RIBBON_FIELD_FRAGMENT_SHADER);
    const program = gl.createProgram();

    if (!program) {
        return null;
    }

    gl.attachShader(program, vertex);
    gl.attachShader(program, fragment);
    gl.linkProgram(program);

    if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
        throw new Error(gl.getProgramInfoLog(program) || 'Ribbon Field program link failed');
    }

    gl.useProgram(program);

    const buffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
    gl.bufferData(
        gl.ARRAY_BUFFER,
        new Float32Array([-1, -1, 1, -1, -1, 1, -1, 1, 1, -1, 1, 1]),
        gl.STATIC_DRAW,
    );
    const position = gl.getAttribLocation(program, 'position');
    gl.enableVertexAttribArray(position);
    gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

    const resolution = gl.getUniformLocation(program, 'resolution');
    const time = gl.getUniformLocation(program, 'time');
    const pointerUniform = gl.getUniformLocation(program, 'pointer');

    let mouseX = 0.72;
    let mouseY = 0.42;
    let targetX = 0.72;
    let targetY = 0.42;
    let frame = 0;
    let visible = true;
    const startedAt = performance.now();

    const pointer = (event) => {
        const bounds = host.getBoundingClientRect();
        targetX = 0.72 + ((event.clientX - bounds.left) / Math.max(bounds.width, 1) - 0.72) * options.pointerAmount;
        targetY =
            0.42 + (1 - (event.clientY - bounds.top) / Math.max(bounds.height, 1) - 0.42) * options.pointerAmount;
    };

    const resize = () => {
        const bounds = host.getBoundingClientRect();
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.max(1, Math.floor(bounds.width * ratio));
        canvas.height = Math.max(1, Math.floor(bounds.height * ratio));
        gl.viewport(0, 0, canvas.width, canvas.height);
        gl.uniform2f(resolution, canvas.width, canvas.height);
    };

    const render = (now) => {
        mouseX += (targetX - mouseX) * options.smoothing;
        mouseY += (targetY - mouseY) * options.smoothing;
        gl.uniform1f(time, (now - startedAt) * 0.001 * options.speed);
        gl.uniform2f(pointerUniform, mouseX, mouseY);
        gl.drawArrays(gl.TRIANGLES, 0, 6);
        frame = visible && !document.hidden ? requestAnimationFrame(render) : 0;
    };

    const resizeObserver = new ResizeObserver(resize);
    const intersection = new IntersectionObserver(([entry]) => {
        visible = entry?.isIntersecting ?? true;

        if (visible && !frame) {
            frame = requestAnimationFrame(render);
        }

        if (!visible && frame) {
            cancelAnimationFrame(frame);
            frame = 0;
        }
    });

    resizeObserver.observe(host);
    intersection.observe(host);

    // The background sits behind the page content, so track the pointer on the
    // window and map it into the host's bounds — same maths as the original
    // host-level listener, just able to receive events across the whole page.
    window.addEventListener('pointermove', pointer, { passive: true });
    resize();
    frame = requestAnimationFrame(render);

    return () => {
        if (frame) {
            cancelAnimationFrame(frame);
        }

        resizeObserver.disconnect();
        intersection.disconnect();
        window.removeEventListener('pointermove', pointer);
        gl.deleteBuffer(buffer);
        gl.deleteShader(vertex);
        gl.deleteShader(fragment);
        gl.deleteProgram(program);
        canvas.remove();
    };
}
