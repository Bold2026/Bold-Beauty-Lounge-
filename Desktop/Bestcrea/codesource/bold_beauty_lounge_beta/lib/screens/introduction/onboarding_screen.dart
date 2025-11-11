import 'package:flutter/material.dart';

import '../../main.dart';

class OnBoardingScreen extends StatefulWidget {
  const OnBoardingScreen({super.key});

  @override
  State<OnBoardingScreen> createState() => _OnBoardingScreenState();
}

class _OnBoardingScreenState extends State<OnBoardingScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;
  bool _isReady = false;

  static const List<_OnboardingSlide> _slides = [
    _OnboardingSlide(
      asset: 'assets/logo/app_icon.png',
      title: 'Bienvenue chez Bold',
      description:
          'Un univers de beauté et de bien-être au cœur de Casablanca.',
      isLogo: true,
    ),
    _OnboardingSlide(
      asset: 'assets/onboarding/onglerie.jpg',
      title: 'Onglerie Signature',
      description:
          'Des finitions impeccables et des designs raffinés pour sublimer vos mains.',
    ),
    _OnboardingSlide(
      asset: 'assets/onboarding/head_spa.jpg',
      title: 'Head Spa Signature',
      description:
          'Un rituel sensoriel qui apaise le cuir chevelu et réveille vos sens.',
    ),
  ];

  void _handleSkip() {
    _goToHome();
  }

  void _handleNext() {
    if (_currentPage == _slides.length - 1) {
      _goToHome();
      return;
    }
    _pageController.nextPage(
      duration: const Duration(milliseconds: 500),
      curve: Curves.easeOutCubic,
    );
  }

  void _goToHome() {
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const MainNavigationWidget()),
    );
  }

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 3)).then((_) {
      if (mounted) {
        setState(() => _isReady = true);
      }
    });
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              child: Row(
                children: [
                  const Text(
                    'Bold Beauty Lounge',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      letterSpacing: 0.8,
                      color: Colors.white,
                    ),
                  ),
                  const Spacer(),
                  TextButton(
                    onPressed: _handleSkip,
                    child: const Text(
                      'Passer',
                      style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 15,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: AnimatedSwitcher(
                duration: const Duration(milliseconds: 500),
                child: _isReady
                    ? PageView.builder(
                        key: const ValueKey('onboarding_pages'),
                        controller: _pageController,
                        itemCount: _slides.length,
                        onPageChanged: (index) {
                          setState(() => _currentPage = index);
                        },
                        itemBuilder: (context, index) {
                          final slide = _slides[index];
                          return _OnboardingPage(slide: slide);
                        },
                      )
                    : const _OnboardingPlaceholder(),
              ),
            ),
            const SizedBox(height: 16),
            _PageIndicator(currentIndex: _currentPage, length: _slides.length),
            const SizedBox(height: 24),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: ElevatedButton(
                onPressed: _handleNext,
                style: ElevatedButton.styleFrom(
                  elevation: 0,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  backgroundColor: Colors.white,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
                child: Text(
                  _currentPage == _slides.length - 1
                      ? 'Commencer'
                      : 'Continuer',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
            const SizedBox(height: 28),
          ],
        ),
      ),
    );
  }
}

class _OnboardingPage extends StatelessWidget {
  const _OnboardingPage({required this.slide});

  final _OnboardingSlide slide;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const SizedBox(height: 16),
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(32),
                boxShadow: [
                  BoxShadow(
                    color: Colors.white.withValues(alpha: 0.08),
                    blurRadius: 24,
                    offset: const Offset(0, 12),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(32),
                child: slide.isLogo
                    ? Container(
                        color: Colors.black,
                        alignment: Alignment.center,
                        child: Container(
                          width: 180,
                          height: 180,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: const Color(0xFFE9D7C2).withOpacity(0.85),
                              width: 4,
                            ),
                            boxShadow: [
                              BoxShadow(
                                color:
                                    const Color(0xFFE9D7C2).withOpacity(0.35),
                                blurRadius: 24,
                                spreadRadius: 4,
                              ),
                            ],
                          ),
                          padding: const EdgeInsets.all(24),
                          child: Image.asset(
                            slide.asset,
                            fit: BoxFit.contain,
                          ),
                        ),
                      )
                    : Image.asset(
                        slide.asset,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) {
                          return Container(
                            color: const Color(0xFF0E0E0E),
                            alignment: Alignment.center,
                            child: const Icon(
                              Icons.image_not_supported_outlined,
                              color: Colors.white38,
                              size: 48,
                            ),
                          );
                        },
                      ),
              ),
            ),
          ),
          const SizedBox(height: 32),
          Center(
            child: Text(
              slide.title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
          ),
          const SizedBox(height: 12),
          Center(
            child: Text(
              slide.description,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 15,
                color: Colors.white70,
                height: 1.5,
              ),
            ),
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

class _PageIndicator extends StatelessWidget {
  const _PageIndicator({required this.currentIndex, required this.length});

  final int currentIndex;
  final int length;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(length, (index) {
        final isActive = index == currentIndex;
        return AnimatedContainer(
          duration: const Duration(milliseconds: 300),
          margin: const EdgeInsets.symmetric(horizontal: 6),
          height: 8,
          width: isActive ? 28 : 10,
          decoration: BoxDecoration(
            color: isActive ? Colors.white : Colors.white24,
            borderRadius: BorderRadius.circular(8),
            boxShadow: isActive
                ? [
                    BoxShadow(
                      color: Colors.white.withValues(alpha: 0.5),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ]
                : null,
          ),
        );
      }),
    );
  }
}

class _OnboardingSlide {
  const _OnboardingSlide({
    required this.asset,
    required this.title,
    required this.description,
    this.isLogo = false,
  });

  final String asset;
  final String title;
  final String description;
  final bool isLogo;
}

class _OnboardingPlaceholder extends StatelessWidget {
  const _OnboardingPlaceholder();

  @override
  Widget build(BuildContext context) {
    return Container(
      alignment: Alignment.center,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: const [
          CircularProgressIndicator(color: Colors.white),
          SizedBox(height: 20),
          Text(
            'Bold Beauty Lounge',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
