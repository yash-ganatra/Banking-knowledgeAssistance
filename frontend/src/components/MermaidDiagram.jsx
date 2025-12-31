import React, { Component, useEffect, useRef, useState } from 'react';
import mermaid from 'mermaid';

mermaid.initialize({
    startOnLoad: false,
    theme: 'default',
    securityLevel: 'loose',
});

class MermaidErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        console.error("Mermaid ErrorBoundary caught an error", error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="text-red-500 text-xs p-2 border border-red-200 rounded bg-red-50 font-mono">
                    Cannot render diagram yet...
                </div>
            );
        }
        return this.props.children;
    }
}

export const MermaidDiagramInternal = ({ code }) => {
    const elementRef = useRef(null);
    const [svg, setSvg] = useState('');
    const [isRendering, setIsRendering] = useState(false);
    const [renderError, setRenderError] = useState(false);

    useEffect(() => {
        if (!code || !code.trim()) {
            setSvg('');
            return;
        }

        // Debounce rendering to handle streaming inputs
        const timeoutId = setTimeout(async () => {
            // If component unmounted or code changed, simple check (though closure captures 'code')
            if (!elementRef.current) return;

            setIsRendering(true);
            setRenderError(false);

            try {
                // Initialize if needed (though we do it globally)
                // mermaid.initialize({ startOnLoad: false, ... });

                const id = `mermaid-${Math.random().toString(36).substr(2, 9)}`;
                // mermaid.render returns objects with svg property
                const { svg: renderedSvg } = await mermaid.render(id, code);
                setSvg(renderedSvg);
            } catch (error) {
                console.debug('Mermaid render error:', error);
                setRenderError(true);
            } finally {
                setIsRendering(false);
            }
        }, 500); // 500ms debounce

        return () => clearTimeout(timeoutId);
    }, [code]);

    // If no code, or whitespace only, render nothing
    if (!code || !code.trim()) return null;

    // If error, fallback to code block
    if (renderError) {
        return (
            <div className="my-4 p-4 rounded-lg bg-red-50 border border-red-100 text-xs font-mono text-red-600 overflow-x-auto whitespace-pre">
                <div className="mb-2 font-semibold">Diagram Render Error</div>
                {code}
            </div>
        );
    }

    // Only render the container if we have content or are waiting
    if (!svg && !isRendering) return null;

    return (
        <div
            ref={elementRef}
            className="my-4 flex justify-center bg-white p-4 rounded-lg border border-gray-100 shadow-sm overflow-x-auto min-h-[50px] transition-all"
            dangerouslySetInnerHTML={{ __html: svg }}
            style={{ opacity: isRendering ? 0.5 : 1 }}
        />
    );
};

export const MermaidDiagram = (props) => (
    <MermaidErrorBoundary>
        <MermaidDiagramInternal {...props} />
    </MermaidErrorBoundary>
);
