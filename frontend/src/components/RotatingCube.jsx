import { motion, useMotionValue, useTransform, useSpring } from "framer-motion";
import { useEffect } from "react";

export function RotatingCube() {
    // Cube size: w-72 (288px). Half size (translateZ) is 144px.
    const halfSize = "144px";

    const x = useMotionValue(0);
    const y = useMotionValue(0);

    // Smooth spring animation
    const springConfig = { damping: 20, stiffness: 100 };
    const rotateX = useSpring(useTransform(y, [0, window.innerHeight], [45, -45]), springConfig);
    const rotateY = useSpring(useTransform(x, [0, window.innerWidth], [-45, 45]), springConfig);

    useEffect(() => {
        function handleMouse(e) {
            x.set(e.clientX);
            y.set(e.clientY);
        }

        window.addEventListener("mousemove", handleMouse);
        return () => window.removeEventListener("mousemove", handleMouse);
    }, [x, y]);

    return (
        <div className="fixed inset-0 z-0 flex items-center justify-center w-screen h-screen overflow-hidden pointer-events-none">
            {/* Gradient glow/shadow behind the cube */}
            <div className="absolute w-96 h-96 bg-primary-500/20 rounded-full blur-3xl" />

            <div className="relative w-72 h-72 perspective-1000">
                <motion.div
                    style={{
                        rotateX,
                        rotateY,
                        transformStyle: "preserve-3d"
                    }}
                    className="w-full h-full preserve-3d relative"
                >
                    <motion.div
                        animate={{
                            rotateX: [0, 360],
                            rotateY: [0, 360],
                        }}
                        transition={{
                            duration: 20,
                            repeat: Infinity,
                            ease: "linear",
                        }}
                        className="w-full h-full preserve-3d relative"
                        style={{ transformStyle: "preserve-3d" }}
                    >
                        {/* Faces common classes for Glass Effect */}
                        {/* bg-primary-500/30 for semi-transparent blue. 
                            backdrop-blur-sm for glass distortion (note: backdrop-blur works on what's BEHIND the element, 
                            which might just be the glow or white background here).
                            border-white/20 for the glass edge. 
                        */}

                        {/* Front - "Customer" */}
                        <div className="absolute inset-0 bg-primary-500/30 backdrop-blur-sm border border-white/20 shadow-inner backface-hidden flex items-center justify-center text-white font-semibold text-4xl"
                            style={{ transform: `translateZ(${halfSize})` }}>
                            Customer
                        </div>

                        {/* Back - "Communication" */}
                        <div className="absolute inset-0 bg-primary-500/30 backdrop-blur-sm border border-white/20 shadow-inner backface-hidden flex items-center justify-center text-white font-semibold text-2xl"
                            style={{ transform: `rotateY(180deg) translateZ(${halfSize})` }}>
                            Communication
                        </div>

                        {/* Right - "Compliance" */}
                        <div className="absolute inset-0 bg-primary-500/30 backdrop-blur-sm border border-white/20 shadow-inner backface-hidden flex items-center justify-center text-white font-semibold text-4xl"
                            style={{ transform: `rotateY(90deg) translateZ(${halfSize})` }}>
                            Compliance
                        </div>

                        {/* Left - "Confidentiality" */}
                        <div className="absolute inset-0 bg-primary-500/30 backdrop-blur-sm border border-white/20 shadow-inner backface-hidden flex items-center justify-center text-white font-semibold text-xl"
                            style={{ transform: `rotateY(-90deg) translateZ(${halfSize})` }}>
                            Confidentiality
                        </div>

                        {/* Top - "Credibility" */}
                        <div className="absolute inset-0 bg-primary-500/30 backdrop-blur-sm border border-white/20 shadow-inner backface-hidden flex items-center justify-center text-white font-semibold text-4xl"
                            style={{ transform: `rotateX(90deg) translateZ(${halfSize})` }}>
                            Credibility
                        </div>

                        {/* Bottom - "Convenience" */}
                        <div className="absolute inset-0 bg-primary-500/30 backdrop-blur-sm border border-white/20 shadow-inner backface-hidden flex items-center justify-center text-white font-semibold text-4xl"
                            style={{ transform: `rotateX(-90deg) translateZ(${halfSize})` }}>
                            Convenience
                        </div>
                    </motion.div>
                </motion.div>
            </div>
        </div>
    );
}
