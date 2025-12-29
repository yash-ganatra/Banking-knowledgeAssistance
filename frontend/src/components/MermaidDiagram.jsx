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

const MermaidDiagramInternal = ({ code }) => {
    const elementRef = useRef(null);
    const [svg, setSvg] = useState('');
    const [isRendering, setIsRendering] = useState(false);

    useEffect(() => {
        // Debounce rendering to handle streaming inputs
        const timeoutId = setTimeout(async () => {
            if (!elementRef.current || !code || isRendering) return;

            // Basic validation to prevent attempting to render obviously incomplete code
            if (!code.includes('graph') && !code.includes('sequence') && !code.includes('flowchart') && !code.includes('classDiagram')) {
                // Allow it to try if we aren't sure, but usually valid charts start with a keyword. 
                // However, streaming might send "gra" then "ph".
                // We rely on catch block, but debounce helps wait for "graph".
            }

            setIsRendering(true);
            try {
                const id = `mermaid-${Math.random().toString(36).substr(2, 9)}`;
                // mermaid.render throws if parsing fails
                const { svg } = await mermaid.render(id, code);
                setSvg(svg);
            } catch (error) {
                // Suppress errors during streaming/typing
                console.debug('Mermaid render warning (handling stream):', error);
                // We do NOT update state to error message here to avoid flickering "Failed" while typing.
                // We just keep the old SVG or empty.
            } finally {
                setIsRendering(false);
            }
        }, 500); // 500ms debounce

        return () => clearTimeout(timeoutId);
    }, [code]);

    return (
        <div
            ref={elementRef}
            className="mermaid my-4 flex justify-center bg-white p-4 rounded-lg border border-gray-100 shadow-sm overflow-x-auto min-h-[50px]"
            dangerouslySetInnerHTML={{ __html: svg }}
        />
    );
};

export const MermaidDiagram = (props) => (
    <MermaidErrorBoundary>
        <MermaidDiagramInternal {...props} />
    </MermaidErrorBoundary>
);
