import 'dart:math';

import 'package:flutter/material.dart';

import '../introduction/onboarding_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late final AnimationController _introController;
  late final AnimationController _pulseController;

  late final Animation<double> _logoScale;
  late final Animation<double> _logoOpacity;
  late final Animation<double> _titleOpacity;
  late final Animation<double> _taglineOpacity;
  late final Animation<double> _bottomOpacity;

  @override
  void initState() {
    super.initState();
    _introController = AnimationController(
      duration: const Duration(milliseconds: 2000),
      vsync: this,
    );

    _pulseController = AnimationController(
      duration: const Duration(milliseconds: 1600),
      vsync: this,
    )..repeat(reverse: true);

    _logoScale = Tween<double>(begin: 0.7, end: 1.0).animate(
      CurvedAnimation(
        parent: _introController,
        curve: const Interval(0.0, 0.65, curve: Curves.easeOutBack),
      ),
    );

    _logoOpacity = CurvedAnimation(
      parent: _introController,
      curve: const Interval(0.0, 0.7, curve: Curves.easeIn),
    );

    _titleOpacity = CurvedAnimation(
      parent: _introController,
      curve: const Interval(0.55, 0.9, curve: Curves.easeIn),
    );

    _taglineOpacity = CurvedAnimation(
      parent: _introController,
      curve: const Interval(0.7, 1.0, curve: Curves.easeIn),
    );

    _bottomOpacity = CurvedAnimation(
      parent: _introController,
      curve: const Interval(0.8, 1.0, curve: Curves.easeIn),
    );

    _introController.forward();

    Future.delayed(const Duration(milliseconds: 2600), () {
      if (mounted) {
        _navigateToOnboarding();
      }
    });
  }

  void _navigateToOnboarding() {
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 600),
        pageBuilder: (_, animation, __) {
          return FadeTransition(
            opacity: CurvedAnimation(
              parent: animation,
              curve: Curves.easeInOut,
            ),
            child: const OnBoardingScreen(),
          );
        },
      ),
    );
  }

  @override
  void dispose() {
    _introController.dispose();
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: AnimatedBuilder(
        animation: Listenable.merge([_introController, _pulseController]),
        builder: (context, child) {
          return Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                colors: [Color(0xFF040404), Color(0xFF12100E), Color(0xFF060606)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
            ),
            child: Stack(
              children: [
                Positioned.fill(
                  child: CustomPaint(
                    painter: _ConstellationPainter(
                      progress: _logoOpacity.value,
                      pulse: _pulseController.value,
                    ),
                  ),
                ),
                SafeArea(
                  child: Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        _buildAnimatedLogo(),
                        const SizedBox(height: 32),
                        FadeTransition(
                          opacity: _titleOpacity,
                          child: const Text(
                            'Bold Beauty Lounge',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 28,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 1.2,
                            ),
                          ),
                        ),
                        const SizedBox(height: 12),
                        FadeTransition(
                          opacity: _taglineOpacity,
                          child: const Text(
                            'Votre beauté, notre passion.',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 15,
                              fontWeight: FontWeight.w400,
                              letterSpacing: 0.6,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        ),
                        const SizedBox(height: 48),
                        FadeTransition(
                          opacity: _bottomOpacity,
                          child: Container(
                            width: 54,
                            height: 2,
                            decoration: BoxDecoration(
                              color: Color(0xFFE9D7C2).withValues(alpha: 0.7),
                              borderRadius: BorderRadius.circular(2),
                              boxShadow: [
                                BoxShadow(
                                  color: Color(0xFFE9D7C2).withValues(alpha: 0.35 * _pulseController.value + 0.15),
                                  blurRadius: 12,
                                  spreadRadius: 1,
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                Positioned(
                  bottom: 48,
                  left: 0,
                  right: 0,
                  child: FadeTransition(
                    opacity: _bottomOpacity,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: const [
                        SizedBox(height: 12),
                        Text(
                          'Version 1.0.0 Beta',
                          style: TextStyle(
                            color: Colors.white38,
                            fontSize: 12,
                            letterSpacing: 1.1,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildAnimatedLogo() {
    return ScaleTransition(
      scale: _logoScale,
      child: FadeTransition(
        opacity: _logoOpacity,
        child: AnimatedBuilder(
          animation: _pulseController,
          builder: (context, child) {
            final glowStrength = 0.35 + (_pulseController.value * 0.35);
            final ringScale = 1.05 + (_pulseController.value * 0.12);
            return SizedBox(
              height: 180,
              width: 180,
              child: Stack(
                alignment: Alignment.center,
                children: [
                  Container(
                    height: 180 * ringScale,
                    width: 180 * ringScale,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(
                        color: Color(0xFFE9D7C2).withValues(alpha: glowStrength),
                        width: 2 + (_pulseController.value * 3),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Color(0xFFE9D7C2).withValues(alpha: glowStrength * 0.8),
                          blurRadius: 40,
                          spreadRadius: 12,
                        ),
                      ],
                    ),
                  ),
                  Container(
                    height: 150,
                    width: 150,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: const RadialGradient(
                        colors: [Color(0xFF161616), Color(0xFF060606)],
                      ),
                      border: Border.all(
                        color: Colors.white.withValues(alpha: 0.08),
                        width: 1.2,
                      ),
                    ),
                  ),
                  Container(
                    height: 120,
                    width: 120,
                    decoration: BoxDecoration(
                      color: Colors.black,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.6),
                          blurRadius: 20,
                          offset: const Offset(0, 6),
                        ),
                      ],
                      border: Border.all(
                        color: Colors.white.withValues(alpha: 0.15),
                        width: 1.5,
                      ),
                    ),
                    child: ClipOval(
                      child: Image.asset(
                        'assets/logo/logo1.png',
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return Icon(
                            Icons.spa,
                            color: Colors.white.withValues(alpha: 0.85),
                            size: 56,
                          );
                        },
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}

class _ConstellationPainter extends CustomPainter {
  _ConstellationPainter({required this.progress, required this.pulse});

  final double progress;
  final double pulse;

  final List<Offset> _anchors = const [
    Offset(0.2, 0.25),
    Offset(0.8, 0.22),
    Offset(0.65, 0.6),
    Offset(0.35, 0.68),
    Offset(0.15, 0.82),
    Offset(0.9, 0.78),
    Offset(0.5, 0.42),
  ];

  @override
  void paint(Canvas canvas, Size size) {
    final backgroundPaint = Paint()
      ..shader = RadialGradient(
        colors: [
          Color(0xFF1A1612).withValues(alpha: 0.6 * progress),
          Colors.transparent,
        ],
        radius: 0.9,
      ).createShader(Offset(size.width / 2, size.height / 2) & Size.zero);

    canvas.drawRect(Offset.zero & size, backgroundPaint);

    final starPaint = Paint()
      ..color = Color(0xFFE9D7C2).withValues(alpha: 0.25 + (progress * 0.35));

    for (final anchor in _anchors) {
      final position = Offset(anchor.dx * size.width, anchor.dy * size.height);
      canvas.drawCircle(position, 2.5 + pulse * 1.5, starPaint);
    }

    final linkPaint = Paint()
      ..color = Colors.white.withValues(alpha: 0.08 + pulse * 0.05)
      ..strokeWidth = 1.1
      ..style = PaintingStyle.stroke;

    for (int i = 0; i < _anchors.length; i++) {
      final start = Offset(_anchors[i].dx * size.width, _anchors[i].dy * size.height);
      final end = Offset(
        _anchors[(i + 1) % _anchors.length].dx * size.width,
        _anchors[(i + 1) % _anchors.length].dy * size.height,
      );
      final controlOffset = Offset(
        (start.dx + end.dx) / 2 + (sin(i + pulse * pi) * 20),
        (start.dy + end.dy) / 2 + (cos(i + pulse * pi) * 18),
      );
      final path = Path()
        ..moveTo(start.dx, start.dy)
        ..quadraticBezierTo(controlOffset.dx, controlOffset.dy, end.dx, end.dy);
      canvas.drawPath(path, linkPaint);
    }
  }

  @override
  bool shouldRepaint(covariant _ConstellationPainter oldDelegate) {
    return oldDelegate.progress != progress || oldDelegate.pulse != pulse;
  }
}
